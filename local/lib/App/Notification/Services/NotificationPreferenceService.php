<?php

namespace App\Notification\Services;

use App\Notification\Enum\Channels;
use App\Notification\Enum\Types;
use App\Notification\Repository\NotificationChannelRepository;
use App\Notification\Repository\NotificationTypeRepository;
use App\Notification\Repository\UserNotificationPreferenceRepository;

class NotificationPreferenceService
{
    protected readonly NotificationChannelRepository $channelRepository;
    protected readonly NotificationTypeRepository $typeRepository;
    protected readonly UserNotificationPreferenceRepository $preferenceRepoitory;

    public function __construct()
    {
        $this->preferenceRepoitory = new UserNotificationPreferenceRepository;
        $this->typeRepository = new NotificationTypeRepository;
        $this->channelRepository = new NotificationChannelRepository;
    }

    public function createOrUpdate(int $userId, Types $type, Channels $channel, bool $enabled = true): void
    {
        $channelRow = $this->channelRepository->getByCode($channel);
        $typeRow = $this->typeRepository->getByCode($type);
        if (!$channelRow) {
            throw new \RuntimeException("Канал '{$channel->value}' не найден.");
        }
        if (!$typeRow) {
            throw new \RuntimeException("Тип уведомления '{$type->value}' не найден.");
        }
        $this->preferenceRepoitory->savePreference($userId, $typeRow['ID'], $channelRow['ID'], $enabled);
    }

    /**
     * Создает дефолтные подписки на все уведомления по email для пользователя
     */
    public function createDefault(int $userId): void
    {
        $allTypes = $this->typeRepository->getAll();
        foreach ($allTypes as $typeRow) {
            $typeEnum = Types::get($typeRow['CODE']);
            if (!$typeEnum) {
                continue;
            }
            $this->createOrUpdate($userId, $typeEnum, Channels::EMAIL);
        }
    }

    public function getSelected(int $userId, ?Types $type = null): array
    {
        $preferences = $this->preferenceRepoitory->getUserPreferences($userId);

        $grouped = [];

        foreach ($preferences as $preference) {
            if ($preference['ENABLED'] !== 'Y') continue;
            $typeEnum = Types::get($preference['TYPE_CODE']);
            $channelEnum = Channels::get($preference['CHANNEL_CODE']);

            if (!$typeEnum || !$channelEnum) {
                continue;
            }

            if ($type && $type !== $typeEnum) {
                continue;
            }

            $typeKey = $typeEnum->value;

            if (!isset($grouped[$typeKey])) {
                $grouped[$typeKey] = [
                    'type' => $typeEnum,
                    'channels' => [],
                ];
            }

            $grouped[$typeKey]['channels'][] = [
                'channel' => $channelEnum,
                'enabled' => $preference['ENABLED'] === 'Y',
            ];
        }

        return $grouped;
    }


    public function getAll(int $userId): array
    {
        $allTypes = $this->typeRepository->getAll(86400);
        $allChannels = $this->channelRepository->getAll(86400);

        $grouped = [];

        foreach ($allTypes as $typeRow) {
            $typeEnum = Types::get($typeRow['CODE']);
            if (!$typeEnum) {
                continue;
            }

            $channels = [];
            foreach ($allChannels as $channelRow) {
                $channelEnum = Channels::get($channelRow['CODE']);
                if (!$channelEnum) {
                    continue;
                }

                $channels[] = [
                    'channel' => $channelEnum,
                    'name' => $channelRow['NAME'],
                    'enabled' => false,
                ];
            }

            $grouped[$typeEnum->value] = [
                'name' => $typeRow['NAME'],
                'type' => $typeEnum,
                'channels' => $channels,
            ];
        }

        foreach ($this->preferenceRepoitory->getUserPreferences($userId) as $preference) {
            $typeEnum = Types::get($preference['TYPE_CODE']);
            $channelEnum = Channels::get($preference['CHANNEL_CODE']);

            if (!$typeEnum || !$channelEnum) {
                continue;
            }

            foreach ($grouped[$typeEnum->value]['channels'] as &$channelInfo) {
                if ($channelInfo['channel'] === $channelEnum) {
                    $channelInfo['enabled'] = $preference['ENABLED'] === 'Y';
                    break;
                }
            }
        }

        return $grouped;
    }
}
