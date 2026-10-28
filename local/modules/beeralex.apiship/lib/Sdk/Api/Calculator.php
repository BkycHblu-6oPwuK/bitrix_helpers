<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Sdk\Api;

use Beeralex\Apiship\Sdk\Entity\Response\RawResponse;

/**
 * Расширенные методы калькулятора ApiShip.
 * OpenAPI: POST /calculator/intervals
 */
class Calculator extends AbstractExtendedApi
{
    /** Запрашивает у СД доступные интервалы доставки. */
    public function getIntervals(array $body): RawResponse
    {
        return $this->requestPost('calculator/intervals', $body);
    }
}
