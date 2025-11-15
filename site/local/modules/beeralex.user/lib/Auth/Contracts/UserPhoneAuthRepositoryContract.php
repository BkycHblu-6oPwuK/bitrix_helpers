<?php

declare(strict_types=1);

namespace Beeralex\User\Auth\Contracts;

use Beeralex\User\Phone;
use Beeralex\Core\Repository\RepositoryContract;

interface UserPhoneAuthRepositoryContract extends RepositoryContract
{
    public function getByPhone(Phone $phone): ?array;
    public function getByUserId(int $userId): ?array;
    public function incrementAttempts(int $userId): void;
    public function markConfirmed(int $userId): void;
    public function resetAttempts(int $userId): void;
}
