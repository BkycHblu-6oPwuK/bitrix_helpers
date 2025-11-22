<?php

namespace Beeralex\User\Profile;

use Illuminate\Support\Collection;
use Beeralex\Catalog\Basket\BasketFacade;
use Beeralex\Catalog\Helper\OrderHelper;
use Beeralex\Catalog\Checkout\TotalBuilder;
use Beeralex\Catalog\Repository\OrderRepository;
use Beeralex\User\User;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Request;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Internals\BasketTable;
use Beeralex\Catalog\Helper\SearchHelper;
use Beeralex\Core\Service\PaginationService;
use Beeralex\User\Phone;
use Bitrix\Sale\Fuser;

class OrdersBuilder
{
    protected User $user;
    protected Request $request;
    protected array $pagination;
    protected Collection $ordersIds;
    protected OrderRepository $ordersRepository;
    //protected bool $isFullLoad;
    //protected ?array $ordersCount = null;

    public function __construct(OrderRepository $ordersRepository)
    {
        $this->request = Context::getCurrent()->getRequest();
        //$this->isFullLoad = $this->request->get('fullLoad') == 1;
        $this->user = User::current();
        $this->ordersRepository = $ordersRepository;
        $this->setOrdersIds();
        $this->pagination = service(PaginationService::class)->getPagination($this->ordersIds->count(), 5);
    }

    protected function setOrdersIds(): void
    {
        $userId = $this->user->getId();
        $filter = $this->request->get('filter') ? Json::decode($this->request->get('filter')) : [];
        $orderIds = null;
        if (!empty($filter['query'])) {
            $productsIds = SearchHelper::getProductsIds($filter['query'], 999);
            if (!empty($productsIds)) {
                /** @var Query */
                $query = BasketTable::query()->setSelect(['ORDER_ID'])->where('LID', Context::getCurrent()->getSite())->where('FUSER_ID', Fuser::getId())->whereNotNull('ORDER_ID')->whereIn('PRODUCT_ID', $productsIds)->exec();
                while ($basket = $query->fetch()) {
                    $orderIds[] = $basket['ORDER_ID'];
                }
            }
            if (!$orderIds) {
                $this->ordersIds = collect();
                return;
            }
        }
        $this->ordersIds = collect(
            $this->ordersRepository->getOrdersIdsByUser($userId, function (Query &$query) use ($filter, $orderIds) {
                $this->applyOrdersFilter($query, $filter, $orderIds);
            })
        );
    }

    private function applyOrdersFilter(Query &$query, array $filter, ?array $orderIds): void
    {
        if (!empty($filter['date']['from'])) {
            $query->where('DATE_INSERT', '>=', \Bitrix\Main\Type\DateTime::createFromPhp(new \DateTime($filter['date']['from'])));
        }

        if (!empty($filter['date']['to'])) {
            $query->where('DATE_INSERT', '<=', \Bitrix\Main\Type\DateTime::createFromPhp(new \DateTime($filter['date']['to'])));
        }

        if ($orderIds) {
            $query->whereIn('ID', $orderIds);
        }

        // if ($this->isFullLoad) {
        //     $countPaidQuery = clone $query;
        //     $countNotPaidQuery = clone $query;
        //     $this->ordersCount = [
        //         'paid' => $countPaidQuery->where('PAYED', 'Y')->countTotal(true)->exec()->getCount(),
        //         'notPaid' => $countNotPaidQuery->where('PAYED', 'N')->countTotal(true)->exec()->getCount(),
        //     ];
        // }

        if (!empty($filter['isPaid'])) {
            $query->where('PAYED', $filter['isPaid']);
        }
    }

    public function build(): array
    {
        $ordersIds = $this->ordersIds->forPage($this->pagination['currentPage'], $this->pagination['pageSize'])->toArray();
        return [
            'pagination' => $this->pagination,
            'orders'     => empty($ordersIds) ? [] : $this->getOrders($ordersIds),
            //'ordersCount' => $this->ordersCount
        ];
    }

    protected function getOrders(array $ordersIds)
    {
        $orders = $this->ordersRepository->getOrdersByIds($ordersIds);
        $result = [];
        $user = User::current();
        $totalBuilder = new TotalBuilder;
        foreach ($orders as $order) {
            $props = OrderHelper::getPropertyValues($order->getPropertyCollection());
            $phone = $props['PHONE'] ? Phone::normalizeE164($props['PHONE']) : $user->getPhone()?->formatE164() ?? '';
            $name = $props['NAME'] ?: $user->getName();
            $basketData = (new BasketFacade($order->getBasket()))->getBasketData();
            $totalBuilder->build($order, $basketData['summary']);
            $dto = new OrderDTO;
            $dto->id = $order->getId();
            $dto->isPaid = $order->isPaid();
            $dto->isCanceled = $order->isCanceled();
            $dto->isSuccess = !$dto->isCanceled && $dto->isPaid && $order->getField('STATUS_ID') === 'F';
            $dto->status = OrderHelper::getStatusName($order);
            $dto->date = OrderHelper::getDateFormatted($order);
            $dto->items = $basketData['items'];
            $dto->summary = $basketData['summary'];
            $dto->recipient = "{$name}, {$phone}";
            $dto->address = "г. {$props['CITY']}, {$props['ADDRESS']}";

            /** @var ?\Bitrix\Sale\Payment */
            $payment = $order->getPaymentCollection()[0];
            if ($payment) {
                if (!$dto->isCanceled && !$dto->isPaid) {
                    $service  = \Bitrix\Sale\PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
                    $initResult = $service->initiatePay($payment, $this->request, \Bitrix\Sale\PaySystem\BaseServiceHandler::STRING);
                    $dto->paymentLink = $initResult->getPaymentUrl();
                }
                $dto->payment = $payment->getPaymentSystemName();
            }

            /** @var ?\Bitrix\Sale\Shipment */
            $shipment = $order->getShipmentCollection()[0];
            if ($shipment) {
                $dto->delivery = $shipment->getDelivery()->getName();
            }

            $result[] = $dto;
        }
        return $result;
    }
}
