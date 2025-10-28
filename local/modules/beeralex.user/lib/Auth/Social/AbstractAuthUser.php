<?php
namespace Beeralex\User\Auth\Social;

use Beeralex\User\Auth\Social\Contracts\AuthUserInterface;

abstract class AbstractAuthUser implements AuthUserInterface
{
    public function toBitrixArray(): array
    {
        return [
            'XML_ID' => $this->getExternalId(),
            'LOGIN' => $this->getLogin(),
            'NAME' => $this->getName(),
            'LAST_NAME' => $this->getLastName(),
            'EMAIL' => $this->getEmail(),
            'EXTERNAL_AUTH_ID' => static::class,
        ];
    }
}
