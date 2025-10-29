<?php
namespace Beeralex\Notification\Repository;

use Beeralex\Core\Repository\Repository;
use Beeralex\Notification\Contracts\NotificationCodeRepositoryContract;
use Beeralex\Notification\Tables\NotificationCodeTable;
use Bitrix\Main\Type\DateTime;

class NotificationCodeRepository extends Repository implements NotificationCodeRepositoryContract
{
    public function __construct()
    {
        parent::__construct(NotificationCodeTable::class);
    }

    public function getValidCode(string $recipient, string $purpose, string $channel): ?array
    {
        return $this->one([
            '=RECIPIENT' => $recipient,
            '=PURPOSE'   => $purpose,
            '=CHANNEL'   => $channel,
            '=USED'      => 'N',
            '>EXPIRES_AT' => new DateTime(),
        ]);
    }

    public function hasValidCode(string $recipient, string $purpose, string $channel): bool
    {
        return (bool)$this->getValidCode($recipient, $purpose, $channel);
    }

    public function markAsUsed(int $id): void
    {
        $this->update($id, ['USED' => 'Y']);
    }

    public function deleteExpiredCodes(): void
    {
        $expired = $this->all(['<EXPIRES_AT' => new DateTime()]);
        foreach ($expired as $code) {
            $this->delete($code['ID']);
        }
    }

    public function getUserCodes(int $userId, int $limit = 10): array
    {
        return $this->all(
            ['USER_ID' => $userId],
            ['*'],
            ['ID' => 'DESC']
        );
    }
}
