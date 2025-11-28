<?php

namespace Beeralex\Notification;

use Beeralex\Notification\Contracts\NotificationChannelContract;
use Beeralex\Notification\DTO\NotificationMessage;
use Beeralex\Notification\Contracts\UserNotificationPreferenceRepositoryContract;
use Beeralex\Notification\Contracts\NotificationTypeRepositoryContract;
use Beeralex\Notification\Contracts\NotificationChannelRepositoryContract;
use Beeralex\Notification\Contracts\NotificationLinkEventTypeRepositoryContract;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class NotificationManager
{
    protected UserNotificationPreferenceRepositoryContract $preferenceRepo;
    protected NotificationTypeRepositoryContract $typeRepo;
    protected NotificationLinkEventTypeRepositoryContract $typeLinkRepo;
    protected NotificationChannelRepositoryContract $channelRepo;

    public function __construct()
    {
        $this->preferenceRepo = service(UserNotificationPreferenceRepositoryContract::class);
        $this->typeRepo = service(NotificationTypeRepositoryContract::class);
        $this->typeLinkRepo = service(NotificationLinkEventTypeRepositoryContract::class);
        $this->channelRepo = service(NotificationChannelRepositoryContract::class);
    }

    /**
     * Рассылает уведомление пользователю по всем разрешённым каналам
     */
    public function notify(NotificationMessage $message): void
    {
        $types = $this->typeLinkRepo->getByEventName($message->eventName);
        foreach ($types as $type) {
            if (!$type['EVENT_TYPE_CODE']) {
                continue;
            }

            $activeChannels = $this->channelRepo->getActiveChannels();
            foreach ($activeChannels as $channel) {
                $isEnabled = $this->preferenceRepo
                    ->isEnabled($message->userId, $type['ID'], $channel['ID']);
                if ($isEnabled) {
                    $this->sendToChannel($channel['CODE'], $message);
                }
            }
        }
    }

    protected function sendToChannel(string $code, NotificationMessage $message): void
    {
        try {
            $channel = ChannelFactory::createChannel($code);
            if (!$channel) {
                return;
            }
            $event = new Event('beeralex.notification', 'OnBeforeSendToChannel', [
                'channel' => $channel,
                'message' => $message,
            ]);
            $event->send();
            /**
             * EventManager::getInstance()->addEventHandler(
             *   'beeralex.notification',
             *   'OnBeforeSendToChannel',
             *       function(NotificationChannelContract $customChannelInstance, NotificationMessage $notificationMessageInstance) {
             *           return new \Bitrix\Main\EventResult(
             *               \Bitrix\Main\EventResult::SUCCESS,
             *               [
             *                 'channel' => $customChannelInstance,
             *                 'message' => $notificationMessageInstance,
             *              ]
             *           );
             *       }
             *   );
             */
            $isSend = false;
            $result = null;
            foreach ($event->getResults() as $eventResult) {
                if ($eventResult->getType() === EventResult::SUCCESS) {
                    $parameters = $eventResult->getParameters();
                    $channelParameter = $parameters['channel'];
                    $messageParameter = $parameters['message'];
                    if ($channelParameter instanceof NotificationChannelContract && $messageParameter instanceof NotificationMessage) {
                        $result = $channelParameter->send($messageParameter);
                        $isSend = true;
                    }
                }
            }
            if (!$isSend) {
                $result = $channel->send($message);
            }
            if (!$result?->isSuccess()) {
                log('Ошибка при отправке уведомления в канал ' . $code . ': ' . implode('; ', $result->getErrorMessages()), 6, true);
            }
        } catch (\Throwable $e) {
            log('Ошибка при отправке уведомления в канал ' . $code . ': ' . $e->getMessage(), 6, true);
        }
    }
}
