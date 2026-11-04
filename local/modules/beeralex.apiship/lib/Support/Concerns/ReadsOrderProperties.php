<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support\Concerns;

use Bitrix\Sale\PropertyValueCollectionBase;

trait ReadsOrderProperties
{
    protected function propertyValue(PropertyValueCollectionBase $properties, string $code): ?string
    {
        $value = $properties->getItemByOrderPropertyCode($code)?->getValue();
        return $value !== null && $value !== '' ? (string)$value : null;
    }
}