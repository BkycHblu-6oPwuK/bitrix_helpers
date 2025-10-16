<?php

namespace App\User\Auth\Repository;

use App\User\Auth\Contracts\ExternalAuthRepositoryContract;
use App\User\Auth\Table\ExternalAuthTable;
use Beeralex\Core\Repository\Repository;

class ExternalAuthRepository extends Repository implements ExternalAuthRepositoryContract
{
    public function __construct()
    {
        parent::__construct(ExternalAuthTable::class);
    }

    public function getByUserAndService(int $userId, string $service): ?array
    {
        return $this->one([
            '=USER_ID' => $userId,
            '=SERVICE' => $service,
        ]);
    }

    public function getByExternalId(string $externalId, string $service): ?array
    {
        return $this->one([
            '=EXTERNAL_ID' => $externalId,
            '=SERVICE' => $service,
        ]);
    }

    public function saveLink(
        int $userId,
        string $service,
        string $externalId,
        ?string $accessToken = null,
        ?string $refreshToken = null
    ): int {
        $existing = $this->getByExternalId($externalId, $service);

        $data = [
            'USER_ID' => $userId,
            'SERVICE' => $service,
            'EXTERNAL_ID' => $externalId,
            'ACCESS_TOKEN' => $accessToken,
            'REFRESH_TOKEN' => $refreshToken,
            'UPDATED_AT' => new \Bitrix\Main\Type\DateTime(),
        ];

        if ($existing) {
            $this->update($existing['ID'], $data);
            return (int)$existing['ID'];
        }

        $data['CREATED_AT'] = new \Bitrix\Main\Type\DateTime();
        return $this->add($data);
    }

    public function deleteByUserAndService(int $userId, string $service): void
    {
        $record = $this->getByUserAndService($userId, $service);
        if ($record) {
            $this->delete($record['ID']);
        }
    }
}
