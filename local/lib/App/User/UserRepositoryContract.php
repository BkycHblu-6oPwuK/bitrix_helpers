<?php

namespace App\User;

use App\User\Phone\Phone;
use Beeralex\Core\Repository\RepositoryContract;

interface UserRepositoryContract extends RepositoryContract
{
    /** поля для выборки из CUser::GetList() */
    const FIELD_SELECT_DEFAULT = [
        'FIELDS' => [
            'ID',
            'NAME',
            'LAST_NAME',
            'SECOND_NAME',
            'EMAIL',
            'PERSONAL_PHONE',
            'PERSONAL_BIRTHDAY',
            'PERSONAL_GENDER',
            'PASSWORD',
            'CHECKWORD'
        ]
    ];
    public function getByEmail(string $email, array $select = []): ?User;
    public function getByPhone(Phone $phone, array $select = []): ?User;
}
