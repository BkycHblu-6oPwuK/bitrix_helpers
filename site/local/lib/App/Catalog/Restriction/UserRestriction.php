<?php

namespace App\Restriction;

use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Services\Base\Restriction;

class UserRestriction extends Restriction
{
    public static function check($current, array $restrictionParams, $service = null): bool
    {
        $allowedUsers = $restrictionParams['USER_IDS'];
        $allowTestUsers = $restrictionParams['ALLOW_TEST_USERS'] === 'Y';

        if (!$current) {
            return false;
        }

        if ($allowTestUsers) {
            global $USER;
            if ($USER->IsAdmin()) {
                return true;
            }
        }

        if (!empty($allowedUsers) && in_array($current, $allowedUsers)) {
            return true;
        }

        if (empty($allowedUsers) && !$allowTestUsers) {
            return true;
        }

        return false;
    }

    public static function getParamsStructure($entityId = 0): array
    {
        return [
            'USER_IDS' => [
                'TYPE' => 'STRING',
                'MULTIPLE' => 'Y',
                'LABEL' => 'ID пользователей (ввод вручную)',
            ],
            'ALLOW_TEST_USERS' => [
                'TYPE' => 'Y/N',
                'LABEL' => 'Разрешить тестовых пользователей',
                'DEFAULT' => 'N',
            ],
        ];
    }

    public static function extractParams(Entity $entity): int
    {
        $order = null;
        if ($entity instanceof Payment || $entity instanceof Shipment) {
            $order = $entity->getOrder();
        } elseif ($entity instanceof Order) {
            $order = $entity;
        }

        if (!$order) {
            return 0;
        }

        $userId = (int)$order->getUserId();
        if ($userId > 0) {
            return $userId;
        }

        if ($order->isNew()) {
            global $USER;
            $userId = is_object($USER) && $USER->IsAuthorized() ? (int)$USER->GetID() : 0;
            return $userId;
        }

        return 0;
    }

    public static function getClassTitle(): string
    {
        return 'Ограничение по пользователю';
    }

    public static function getClassDescription(): string
    {
        return 'Ограничивает доступность сервиса для конкретных и/или тестовых пользователей.';
    }

    public static function getCurDir(): string
    {
        return __DIR__;
    }
}
