<?php

namespace App\qrShow;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Sale\Internals\OrderTable;
use Bitrix\Sale\Order;
use App\Catalog\Helper\OrderHelper;

/**
 * @todo Освежить бы код, да шаблон под сборщик
 */
class ItbQrShow extends \CBitrixComponent implements Controllerable
{
    public function executeComponent()
    {
        if (!$this->arParams['ORDER_ID']) {
            ShowError('Не передан ORDER_ID');
            return;
        }
        if ($this->startResultCache()) {
            $this->arResult = $this->setData();
            $this->includeComponentTemplate();
        }
    }

    public function configureActions(): array
    {
        return [
            'checkPayment' => [
                'prefilters' => [],
            ],
        ];
    }

    /**
     * Заполняет $arResult
     * @return string
     */

    public function setData(): array
    {
        $data = [];
        $order = Order::load($this->arParams['ORDER_ID']);
        $data['QR_URL'] = OrderHelper::initPay($order)->getPaymentUrl();
        $data['ORDER_ID'] = $this->arParams['ORDER_ID'];
        $data['QR'] = base64_encode($this->generate($data['QR_URL']));
        if ($this->arParams['CHECK_PAYMENT'] == 'Y') {
            $data['CHECK_PAYMENT'] = $this->arParams['CHECK_PAYMENT'] == 'Y';
            $data['REDIRECT_URL'] = !empty($this->arParams['REDIRECT_URL']) ? $this->arParams['REDIRECT_URL'] : "";
            $data['ISSET_ORDER_ID'] = !empty($this->arParams['REDIRECT_URL']) ? (bool) (strpos($data['REDIRECT_URL'], '#ORDER_ID#') !== false) : false;
            $data['REDIRECT_URL'] = $data['ISSET_ORDER_ID'] ? str_replace('#ORDER_ID#', '', $data['REDIRECT_URL']) : $data['REDIRECT_URL'];
        }
        return $data;
    }

    /**
     * Генирирует qr код из ссылки используя библиотеку BaconQrCode
     * @return string
     */

    public function generate(string $url): string
    {
        $string = "";
        if (!empty($url)) {
            $renderer = new ImageRenderer(
                new RendererStyle(200),
                new ImagickImageBackEnd()
            );
            $writer = new Writer($renderer);

            $string = $writer->writeString($url);

        }
        return $string;
    }

    /**
     * Проверяет статус оплаты по id заказа
     * Можно передать файл и функцию проверяющая оплату
     * @param string $requestData
     * @return array
     */

    public function checkPaymentAction($requestData): array
    {
        $params = static::parseData($requestData);
        $result = [];
        if ($params['orderId']) {
            $params['orderId'] ??= 0;
            $isPayed = OrderTable::query()->where('ID', $params['orderId'])->setSelect(['PAYED'])->fetch()['PAYED'] == 'Y' ? true : false;
            $result["paid"] = $isPayed;
        }

        return $result;
    }

    /**
     * @param string $requestData
     * @return array
     */
    public static function parseData($requestData): array
    {
        $params = [];
        preg_match_all("/(\w+)='(.*?)'/", $requestData, $matches);
        $params = array_combine($matches[1], $matches[2]);
        return $params;
    }
}
