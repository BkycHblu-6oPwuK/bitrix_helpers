<?php
namespace Itb\Checkout;

use Bitrix\Sale\PropertyValueCollectionBase;
use Itb\Catalog\Order;

class FormBuilder
{

    public function buildForCollection(PropertyValueCollectionBase $collection): FormDTO
    {
        $props = Order::getPropertyValues($collection);

        $dto = new FormDTO();
        $dto->phone = $props['PHONE'] ?? '';
        $dto->email = $props['EMAIL'] ?? '';
        $dto->fio = $props['FIO'] ?? '';
        $dto->legalInn = $props['INN'] ?? '';
        $dto->legalName = $props['COMPANY'] ?? '';
        $dto->legalAddress = $props['COMPANY_ADR'] ?? '';
        $dto->legalActualAddress = $props['ACTUAL_ADR'] ?? '';
        $dto->legalAddressCheck = $props['LEGAL_ADR_CHECK'] === 'Y' ?? '';

        return $dto;
    }

}
