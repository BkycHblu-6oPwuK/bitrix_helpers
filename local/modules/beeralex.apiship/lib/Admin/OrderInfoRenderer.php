<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Admin;

use Beeralex\Apiship\Enum\DeliveryType;
use Beeralex\Apiship\Enum\PickupType;

/**
 * HTML блока «Доставка ApiShip» в карточке заказа. Только разметка: данные приходят готовыми
 * ({@see \Beeralex\Apiship\Support\OrderInfoReader}), всё выводимое экранируется.
 */
class OrderInfoRenderer
{
    public const AJAX_ACTION = 'beeralex:apiship.api.OrderController.create';

    /** @var array<string,string> */
    protected const LINK_STATUSES = [
        'active'    => 'Активен',
        'cancelled' => 'Отменён',
        'deleted'   => 'Удалён',
    ];

    /** @var array<int,string> */
    protected const WEBHOOK_STATES = [
        0 => 'ожидает',
        1 => 'обработан',
        2 => 'ошибка',
    ];

    /**
     * @param array{order: ?array<string,mixed>, statuses: array<int,array<string,mixed>>, webhooks: array<int,array<string,mixed>>} $info
     * @param ?string $markedError последняя ошибка создания, поставленная модулем (из отметки заказа)
     * @param bool $canCreate показывать ли кнопку «Создать заказ»
     */
    public function render(array $info, int $bitrixOrderId, ?string $markedError, bool $canCreate): string
    {
        $html = '<div class="beeralex-apiship-order">';

        if ($info['order'] === null) {
            $html .= $this->renderNotCreated($bitrixOrderId, $markedError, $canCreate);
        } else {
            $html .= $this->renderOrder($info['order']);
            $html .= $this->renderStatuses($info['statuses']);
            $html .= $this->renderWebhooks($info['webhooks']);
        }

        return $html . '</div>';
    }

    /** JS кнопки; выводится один раз на страницу через getScripts. */
    public function scripts(): string
    {
        $action = json_encode(static::AJAX_ACTION);

        return <<<HTML
            <script>
            window.BeeralexApiship = {
                createOrder: function (orderId, button) {
                    if (!window.BX || !BX.ajax || !BX.ajax.runAction) {
                        alert('Не загружено ядро BX.ajax, обновите страницу.');
                        return;
                    }
                    button.disabled = true;
                    BX.ajax.runAction({$action}, {data: {orderId: orderId}}).then(
                        function () { window.location.reload(); },
                        function (response) {
                            button.disabled = false;
                            var errors = (response && response.errors) || [];
                            alert(errors.map(function (e) { return e.message; }).join('\\n') || 'Не удалось создать заказ в ApiShip');
                        }
                    );
                }
            };
            </script>
        HTML;
    }

    protected function renderNotCreated(int $bitrixOrderId, ?string $markedError, bool $canCreate): string
    {
        $html = '<p>Заказ ещё не создан в ApiShip.</p>';

        if ($markedError !== null) {
            $html .= '<p style="color:#c00"><b>Последняя ошибка:</b> ' . $this->e($markedError) . '</p>';
        }

        if ($canCreate) {
            $html .= '<p><input type="button" class="adm-btn adm-btn-save" value="Создать заказ в ApiShip"'
                . ' onclick="BeeralexApiship.createOrder(' . $bitrixOrderId . ', this)"></p>';
        }

        return $html;
    }

    /** @param array<string,mixed> $o */
    protected function renderOrder(array $o): string
    {
        $o += array_fill_keys([
            'APISHIP_ORDER_ID',
            'STATUS',
            'STATUS_NAME',
            'STATUS_KEY',
            'STATUS_DATE',
            'PROVIDER_KEY',
            'PROVIDER_CONNECT_ID',
            'TARIFF_ID',
            'DELIVERY_TYPE',
            'PICKUP_TYPE',
            'POINT_IN_ID',
            'POINT_OUT_ID',
            'CLIENT_NUMBER',
            'PROVIDER_NUMBER',
            'BARCODE',
            'WEIGHT',
            'ASSESSED_COST',
            'COD_COST',
            'DELIVERY_COST',
            'PICKUP_DATE',
            'PLANNED_DELIVERY_DATE',
            'PLANNED_DELIVERY_TIME_START',
            'PLANNED_DELIVERY_TIME_END',
            'PLANNED_DELIVERY_CHANGED',
            'APISHIP_CREATED_AT',
            'CREATED_AT',
            'TRACKING_URL',
            'CHECK_URL',
        ], null);
        $planned = $this->plannedDelivery($o);

        $rows = [
            'Заказ ApiShip'          => (string)$o['APISHIP_ORDER_ID'],
            'Состояние записи'       => self::LINK_STATUSES[(string)$o['STATUS']] ?? (string)$o['STATUS'],
            'Статус доставки'        => $this->join([$o['STATUS_NAME'], $o['STATUS_KEY'] ? '(' . $o['STATUS_KEY'] . ')' : null, $o['STATUS_DATE']]),
            'Служба доставки'        => $this->join([$o['PROVIDER_KEY'], $o['PROVIDER_CONNECT_ID'] ? '(подключение ' . $o['PROVIDER_CONNECT_ID'] . ')' : null]),
            'Тариф'                  => $o['TARIFF_ID'] ? '#' . $o['TARIFF_ID'] : '',
            'Тип доставки'           => DeliveryType::tryFrom((int)$o['DELIVERY_TYPE'])?->label() ?? '',
            'Тип забора'             => PickupType::tryFrom((int)$o['PICKUP_TYPE'])?->label() ?? '',
            'Пункт приёма / выдачи'  => $this->join([$o['POINT_IN_ID'] ? 'приём ' . $o['POINT_IN_ID'] : null, $o['POINT_OUT_ID'] ? 'выдача ' . $o['POINT_OUT_ID'] : null], ', '),
            'Номер клиента'          => (string)$o['CLIENT_NUMBER'],
            'Номер в СД'             => (string)$o['PROVIDER_NUMBER'],
            'Штрихкод'               => (string)$o['BARCODE'],
            'Вес, г'                 => $o['WEIGHT'] ? (string)$o['WEIGHT'] : '',
            'Оценочная стоимость'    => $this->money($o['ASSESSED_COST']),
            'Наложенный платёж'      => $this->money($o['COD_COST']),
            'Стоимость доставки'     => $this->money($o['DELIVERY_COST']),
            'Дата забора'            => (string)$o['PICKUP_DATE'],
            'Плановая доставка'      => $planned,
            'Создан в ApiShip'       => (string)($o['APISHIP_CREATED_AT'] ?: $o['CREATED_AT']),
        ];

        $html = '<table class="adm-detail-content-table edit-table" style="width:100%">';
        foreach ($rows as $label => $value) {
            if ($value === '') {
                continue;
            }
            $html .= '<tr><td class="adm-detail-content-cell-l" style="width:30%">' . $this->e($label) . ':</td>'
                . '<td class="adm-detail-content-cell-r">' . $this->e($value) . '</td></tr>';
        }
        foreach (['TRACKING_URL' => 'Отслеживание', 'CHECK_URL' => 'Чек / проверка'] as $column => $label) {
            $link = $this->link((string)$o[$column], $label);
            if ($link !== '') {
                $html .= '<tr><td class="adm-detail-content-cell-l">' . $this->e($label) . ':</td>'
                    . '<td class="adm-detail-content-cell-r">' . $link . '</td></tr>';
            }
        }

        return $html . '</table>';
    }

    /** @param array<int,array<string,mixed>> $rows */
    protected function renderStatuses(array $rows): string
    {
        $html = '<h3>История статусов</h3>';
        if ($rows === []) {
            return $html . '<p>Статусов от ApiShip ещё не приходило.</p>';
        }

        $body = '';
        foreach ($rows as $r) {
            $r += array_fill_keys([
                'CREATED',
                'CREATED_AT',
                'STATUS_NAME',
                'STATUS_KEY',
                'DESCRIPTION',
                'ERROR_CODE',
                'PROVIDER_NAME',
                'PROVIDER_DESCRIPTION',
            ], null);
            $status = $this->join([$r['STATUS_NAME'], $r['STATUS_KEY'] ? '(' . $r['STATUS_KEY'] . ')' : null]);
            $provider = $this->join([$r['PROVIDER_NAME'], $r['PROVIDER_DESCRIPTION']], ' — ');
            $description = $this->join([$r['DESCRIPTION'], $r['ERROR_CODE'] ? 'код ошибки: ' . $r['ERROR_CODE'] : null], '. ');

            $body .= $this->tr([
                (string)($r['CREATED'] ?: $r['CREATED_AT']),
                $status,
                $description,
                $provider,
            ]);
        }

        return $html . $this->table(['Когда', 'Статус', 'Описание', 'Статус СД'], $body);
    }

    /** @param array<int,array<string,mixed>> $rows */
    protected function renderWebhooks(array $rows): string
    {
        $html = '<h3>Журнал вебхуков</h3>';
        if ($rows === []) {
            return $html . '<p>Вебхуков по этому заказу не было.</p>';
        }

        $body = '';
        foreach ($rows as $r) {
            $r += array_fill_keys(['CREATED_AT', 'EVENT_TYPE', 'PROCESSED', 'ERROR_MESSAGE'], null);
            $body .= $this->tr([
                (string)$r['CREATED_AT'],
                (string)$r['EVENT_TYPE'],
                self::WEBHOOK_STATES[(int)$r['PROCESSED']] ?? (string)$r['PROCESSED'],
                (string)$r['ERROR_MESSAGE'],
            ], (int)$r['PROCESSED'] === 2);
        }

        return $html . $this->table(['Получен', 'Тип события', 'Обработка', 'Ошибка'], $body);
    }

    /** @param array<string,mixed> $o */
    protected function plannedDelivery(array $o): string
    {
        $date = (string)$o['PLANNED_DELIVERY_DATE'];
        if ($date === '') {
            return '';
        }

        $time = $this->join([$o['PLANNED_DELIVERY_TIME_START'], $o['PLANNED_DELIVERY_TIME_END']], '–');
        $text = $date . ($time !== '' ? ', ' . $time : '');

        return ($o['PLANNED_DELIVERY_CHANGED'] ?? 'N') === 'Y' ? $text . ' (дата изменилась)' : $text;
    }

    /** @param string[] $headers */
    protected function table(array $headers, string $body): string
    {
        $head = '';
        foreach ($headers as $h) {
            $head .= '<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner">' . $this->e($h) . '</div></td>';
        }

        return '<table class="adm-list-table" style="width:100%"><thead><tr class="adm-list-table-header">'
            . $head . '</tr></thead><tbody>' . $body . '</tbody></table>';
    }

    /** @param string[] $cells */
    protected function tr(array $cells, bool $error = false): string
    {
        $html = '<tr class="adm-list-table-row"' . ($error ? ' style="color:#c00"' : '') . '>';
        foreach ($cells as $cell) {
            $html .= '<td class="adm-list-table-cell">' . $this->e($cell) . '</td>';
        }

        return $html . '</tr>';
    }

    /** Ссылка только на http(s): значение приходит из внешней системы. */
    protected function link(string $url, string $text): string
    {
        if (!preg_match('#^https?://#i', $url)) {
            return '';
        }

        return '<a href="' . $this->e($url) . '" target="_blank" rel="noopener noreferrer">' . $this->e($text) . '</a>';
    }

    protected function money(mixed $value): string
    {
        return $value === null || $value === '' ? '' : number_format((float)$value, 2, ',', ' ') . ' ₽';
    }

    /** @param array<int,mixed> $parts */
    protected function join(array $parts, string $glue = ' '): string
    {
        return implode($glue, array_filter(array_map('strval', $parts), static fn(string $p) => trim($p) !== ''));
    }

    protected function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
