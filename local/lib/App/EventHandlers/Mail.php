<?php

namespace App\EventHandlers;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Sale\Order;
use App\Catalog\Helper\OrderHelper;
use App\Notification\Contracts\SmsContract;
use App\Notification\Enum\Channels;
use App\Notification\Enum\NotificationStatuses;
use App\Notification\Services\NotificationPreferenceService;
use App\Notification\SmsTemplates;
use App\User\UserRepository;
use App\User\User;

class Mail
{
    public static function onBeforeAdd(\Bitrix\Main\Entity\Event $event)
    {
        $fields = $event->getParameter("fields");
        $cFields = $fields['C_FIELDS'];
        $user = static::getUser($cFields);
        if (!$user || !$user->getId()) return;
        $result = new \Bitrix\Main\Entity\EventResult();
        $order = static::getOrder($cFields);
        if ($order) {
            $propertyValues = OrderHelper::getPropertyValues($order->getPropertyCollection());
            $cFields['TRACK_NUMBER'] = $propertyValues['TRACK_NUMBER'] ?? '';
            $cFields['ORDER_USER'] = static::getUserName($propertyValues, $user);
        }
        $cFields['USER_ID'] = $user->getId();
        $changedFields = [
            'C_FIELDS' => $cFields,
        ];

        $result->modifyFields($changedFields);

        return $result;
    }

    public static function OnBeforeEventSend($fields, $arTemplate)
    {
        try {
            $fields = collect($fields);
            $type = NotificationStatuses::resolveTypeByCode($arTemplate['EVENT_NAME']);
            if (!$type) return;
            $user = static::getUser($fields->all());
            if (!$user || !$user->getId()) return;
            $statusType = NotificationStatuses::get($arTemplate['EVENT_NAME'], $type);
            if (!$statusType) return;
            $preferences = (new NotificationPreferenceService)->getSelected($user->getId(), $type)[$type->value];
            if (empty($preferences)) return false;
            $hasNotEmail = true;
            $isSms = false;
            foreach ($preferences['channels'] as $channel) {
                if ($channel['channel'] === Channels::EMAIL) {
                    $hasNotEmail = false;
                }
                if ($channel['channel'] === Channels::SMS) {
                    $isSms = true;
                }
            }
            if ($isSms) {
                /**
                 * @var SmsContract $smsService
                 */
                $smsService = ServiceLocator::getInstance()->get(SmsContract::class);
                $keys = $fields->keys()->map(fn ($item) => "#{$item}#")->all();
                $smsService->sendSms($user->getPhone(), SmsTemplates::get($statusType, $keys, $fields->all()));
            }
            if ($hasNotEmail) {
                return false;
            }
        } catch (\Exception $e) {
            toFile([
                'Ошибка в событии OnBeforeEventSend',
                'message' => $e->getMessage()
            ], 'local/logs/sms_error.log');
        }
    }

    protected static function getUserName(array $propertyValues, User $user) : string
    {
        $name = '';
        if($propertyValues['NAME'] && $propertyValues['LAST_NAME']) {
            $name = $propertyValues['NAME'] . ' ' . $propertyValues['LAST_NAME'];
        } elseif($propertyValues['FIO']) {
            $name = $propertyValues['FIO'];
        } else {
            $name = $user->getFullName();
        }
        return $name;
    }

    protected static function getUser(array $fields): ?User
    {
        $userId = 0;
        if ($fields['USER_ID']) {
            $userId = (int)$fields['USER_ID'];
        } elseif ($order = static::getOrder($fields)) {
            $userId = $order->getUserId() ?: 0;
        } /*elseif (User::current()->getId()) {
            return User::current();
        }*/
        return $userId ? (new UserRepository)->getById($userId) : null;
    }

    protected static function getOrder(array $fields): ?Order
    {
        static $orders = [];
        $orderId = (int)$fields['ORDER_ID'];
        try {
            if ($orderId && !$orders[$orderId]) {
                $orders[$orderId] = Order::load($orderId);
            }
            return $orders[$orderId];
        } catch (\Exception $e) {
        }
        return null;
    }
}
