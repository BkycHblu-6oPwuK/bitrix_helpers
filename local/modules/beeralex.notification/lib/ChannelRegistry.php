<?php

namespace Beeralex\Notification;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class ChannelRegistry
{
    protected static array $defaultChannels = [
        Channels\EmailChannel::class,
        Channels\SmsChannel::class,
        Channels\TelegramChannel::class,
    ];

    /**
     * @return array<class-string<\Beeralex\Notification\Contracts\NotificationChannelContract>>
     */
    public static function getAvailableChannels(): array
    {
        $channels = self::$defaultChannels;

        $event = new Event('beeralex.notification', 'OnBuildChannels');
        $event->send();
        /**
         * EventManager::getInstance()->addEventHandler(
         *   'beeralex.notification',
         *   'OnBuildChannels',
         *       function() {
         *           return new \Bitrix\Main\EventResult(
         *               \Bitrix\Main\EventResult::SUCCESS,
         *               [
         *                   \MyNamespace\CustomNotificationChannel::class
         *               ]
         *           );
         *       }
         *   );
         */

        foreach ($event->getResults() as $eventResult) {
            if ($eventResult->getType() === EventResult::SUCCESS) {
                $channelClasses = $eventResult->getParameters();
                foreach ($channelClasses as $class) {
                    if (is_subclass_of($class, Contracts\NotificationChannelContract::class)) {
                        $channels[] = $class;
                    }
                }
            }
        }

        return $channels;
    }
}
