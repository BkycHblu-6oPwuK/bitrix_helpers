<?php

namespace Itb\Notification\Repository;

use Itb\Notification\Enum\Types;
use Itb\Notification\Tables\UserNotificationPreferenceTable;

class UserNotificationPreferenceRepository
{
    /**
     * @var UserNotificationPreferenceTable|string $entity
     */
    protected readonly string $entity;

    public function __construct()
    {
        $this->entity = UserNotificationPreferenceTable::class;
    }

    public function getUserPreferences(int $userId): array
    {
        return $this->entity::getList([
            'filter' => ['USER_ID' => $userId],
            'select' => [
                'USER_ID',
                'NOTIFICATION_TYPE_ID',
                'TYPE_CODE' => 'TYPE.CODE',
                'CHANNEL_ID',
                'CHANNEL_CODE' => 'CHANNEL.CODE',
                'ENABLED'
            ],
        ])->fetchAll();
    }

    public function getEnabledChannelsForUserAndType(int $userId, Types $type): array
    {
        return $this->entity::getList([
            'filter' => [
                'USER_ID' => $userId,
                'ENABLED' => 'Y',
                'TYPE.CODE' => $type->value
            ],
            'select' => ['CHANNEL.CODE']
        ])->fetchAll();
    }

    public function savePreference(int $userId, int $typeId, int $channelId, bool $enabled = true): void
    {
        $primary = [
            'USER_ID' => $userId,
            'NOTIFICATION_TYPE_ID' => $typeId,
            'CHANNEL_ID' => $channelId
        ];

        $fields = [
            'ENABLED' => $enabled ? 'Y' : 'N'
        ];

        $existing = $this->entity::getByPrimary($primary)->fetch();

        if ($existing) {
            $result = $this->entity::update($primary, $fields);
        } else {
            $result = $this->entity::add(array_merge($primary, $fields));
        }
        if (!$result->isSuccess()) {
            throw new \RuntimeException("Ошибка при сохранении");
        }
    }

    public function deletePreference(int $userId, int $typeId, int $channelId): void
    {
        $this->entity::delete([
            'USER_ID' => $userId,
            'NOTIFICATION_TYPE_ID' => $typeId,
            'CHANNEL_ID' => $channelId
        ]);
    }
}
