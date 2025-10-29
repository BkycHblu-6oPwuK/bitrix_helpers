<?php

namespace Beeralex\Notification\Repository;

use Beeralex\Core\Repository\Repository;
use Beeralex\Notification\Contracts\UserNotificationPreferenceRepositoryContract;
use Beeralex\Notification\Tables\UserNotificationPreferenceTable;

class UserNotificationPreferenceRepository extends Repository implements UserNotificationPreferenceRepositoryContract
{
    public function __construct()
    {
        parent::__construct(UserNotificationPreferenceTable::class);
    }

    public function getByUser(int $userId): array
    {
        return $this->all(['=USER_ID' => $userId]);
    }

    public function getUserPreference(int $userId, int $notificationTypeId, int $channelId): ?array
    {
        return $this->one([
            '=USER_ID' => $userId,
            '=NOTIFICATION_TYPE_ID' => $notificationTypeId,
            '=CHANNEL_ID' => $channelId,
        ]);
    }

    public function isEnabled(int $userId, int $notificationTypeId, int $channelId): bool
    {
        $pref = $this->getUserPreference($userId, $notificationTypeId, $channelId);
        return $pref ? $pref['ENABLED'] === 'Y' : false;
    }

    public function setEnabled(int $userId, int $notificationTypeId, int $channelId, bool $enabled): void
    {
        $existing = $this->getUserPreference($userId, $notificationTypeId, $channelId);

        $data = [
            'USER_ID' => $userId,
            'NOTIFICATION_TYPE_ID' => $notificationTypeId,
            'CHANNEL_ID' => $channelId,
            'ENABLED' => $enabled ? 'Y' : 'N',
        ];

        if ($existing) {
            $this->updateCompositeKey($data);
        } else {
            $this->add($data);
        }
    }

    public function updateCompositeKey(array $data): void
    {
        $result = $this->entityClass::update([
            'USER_ID' => $data['USER_ID'],
            'NOTIFICATION_TYPE_ID' => $data['NOTIFICATION_TYPE_ID'],
            'CHANNEL_ID' => $data['CHANNEL_ID'],
        ], $data);

        if (!$result->isSuccess()) {
            throw new \Bitrix\Main\SystemException(implode(', ', $result->getErrorMessages()));
        }
    }
}
