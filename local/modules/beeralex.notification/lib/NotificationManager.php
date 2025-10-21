<?php

namespace Beeralex\Notification;

use Beeralex\Notification\Contracts\NotificationChannelContract;
use Beeralex\Notification\DTO\NotificationMessage;
use Beeralex\Notification\Contracts\UserNotificationPreferenceRepositoryContract;
use Beeralex\Notification\Contracts\NotificationTypeRepositoryContract;
use Beeralex\Notification\Contracts\NotificationChannelRepositoryContract;
use Bitrix\Main\DI\ServiceLocator;

class NotificationManager
{
    protected UserNotificationPreferenceRepositoryContract $preferenceRepo;
    protected NotificationTypeRepositoryContract $typeRepo;
    protected NotificationChannelRepositoryContract $channelRepo;

    public function __construct()
    {
        $locator = ServiceLocator::getInstance();
        $this->preferenceRepo = $locator->get(UserNotificationPreferenceRepositoryContract::class);
        $this->typeRepo = $locator->get(NotificationTypeRepositoryContract::class);
        $this->channelRepo = $locator->get(NotificationChannelRepositoryContract::class);
    }

    /**
     * Рассылает уведомление пользователю по всем разрешённым каналам
     */
    public function notify(NotificationMessage $message): void
    {
        $type = $this->typeRepo->getByCode($message->eventName);
        if (!$type) {
            return;
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

    protected function sendToChannel(string $code, NotificationMessage $message): void
    {
        $channel = match ($code) {
            'email' => new \Beeralex\Notification\Channels\EmailChannel(),
            'sms' => new \Beeralex\Notification\Channels\SmsChannel(),
            'telegram' => new \Beeralex\Notification\Channels\TelegramChannel(),
            default => null
        };

        if ($channel instanceof NotificationChannelContract) {
            $channel->send($message);
        }
    }
}
