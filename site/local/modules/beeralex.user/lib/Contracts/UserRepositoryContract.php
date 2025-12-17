<?php
declare(strict_types=1);
namespace Beeralex\User\Contracts;

use Beeralex\User\Phone;
use Beeralex\Core\Repository\RepositoryContract;

interface UserRepositoryContract extends RepositoryContract
{
    const FIELD_SELECT_DEFAULT = [
        'ID',
        'NAME',
        'LAST_NAME',
        'SECOND_NAME',
        'EMAIL',
        'PASSWORD',
        'CHECKWORD',
        'PHONE_NUMBER' => 'PHONE_AUTH.PHONE_NUMBER',
        'PERSONAL_BIRTHDAY',
        'PERSONAL_GENDER',
        'PICTURE_SRC'
    ];
    public function getByEmail(string $email, array $select = []): ?UserEntityContract;
    public function getByPhone(Phone $phone, array $select = []): ?UserEntityContract;
    public function getCurrentUser(bool $refresh = false): UserEntityContract;
}
