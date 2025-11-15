<?php
declare(strict_types=1);
namespace Beeralex\User\Auth;

use Beeralex\Core\Repository\Repository;
use Beeralex\User\Auth\Contracts\UserPhoneAuthRepositoryContract;
use Beeralex\User\Phone;
use Bitrix\Main\UserPhoneAuthTable;

/**
 * @extends Repository<UserPhoneAuthTable>
 */
class UserPhoneAuthRepository extends Repository implements UserPhoneAuthRepositoryContract
{
    public function __construct()
    {
        parent::__construct(UserPhoneAuthTable::class);
    }

    public function getByPhone(Phone $phone): ?array
    {
        return $this->one(['=PHONE_NUMBER' => $phone->formatE164()]);
    }

    public function getByUserId(int $userId): ?array
    {
        return $this->getById($userId);
    }

    public function incrementAttempts(int $userId): void
    {
        $data = $this->getByUserId($userId);
        $attempts = (int)($data['ATTEMPTS'] ?? 0) + 1;
        $this->update($userId, ['ATTEMPTS' => $attempts]);
    }

    public function markConfirmed(int $userId): void
    {
        $this->update($userId, ['CONFIRMED' => 'Y', 'DATE_SENT' => '']);
    }

    public function resetAttempts(int $userId): void
    {
        $this->update($userId, ['ATTEMPTS' => 0]);
    }
}
