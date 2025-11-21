<?php

declare(strict_types=1);

namespace Beeralex\User\Auth;

use Beeralex\User\Auth\Contracts\AuthenticatorContract;
use Beeralex\User\Auth\Contracts\AuthValidatorInterface;
use Beeralex\User\Contracts\UserRepositoryContract;
use Beeralex\User\Auth\AuthCredentialsDto;
use Bitrix\Main\Result;

/**
 * Менеджер аутентификации для Bitrix:
 * - Координирует работу различных аутентификаторов (local, OAuth и т.д.)
 * - Единая ответственность: управление процессом аутентификации
 * - Не занимается JWT токенами (это делегировано AuthService)
 */
class AuthManager
{
    /**
     * ключом выступает название интерфейса/ключ аутентификатора
     * @param array<string, AuthenticatorContract> $authenticators
     * @param array<string, AuthValidatorInterface> $validators
     */
    public function __construct(
        public readonly array $authenticators,
        public readonly array $validators = []
    ) {}

    /**
     * Аутентификация пользователя через выбранный аутентификатор.
     * Возвращает userId и тип аутентификатора.
     *
     * @return Result{userId:int, authType:string, email:string|null}
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function authenticate(string $type, AuthCredentialsDto $userDto): Result
    {
        $result = new Result();
        $authenticator = $this->authenticators[$type] ?? null;

        if (!$authenticator) {
            $result->addError(new \Bitrix\Main\Error("Unknown auth type: {$type}"));
            return $result;
        }
        if (!$authenticator->isService() && $userDto->isEmpty()) {
            $result->addError(new \Bitrix\Main\Error("User data must be provided for local authenticators"));
            return $result;
        }

        if (!$userDto->isEmpty()) {
            $validator = $this->validators[$type];
            $validationResult = $validator->validateForLogin($userDto);
            if (!$validationResult->isSuccess()) {
                $result->addErrors($validationResult->getErrors());
                return $result;
            }
        }

        $resultAuth = $authenticator->authenticate($userDto);

        if (!$resultAuth->isSuccess()) {
            return $resultAuth;
        }

        $userId = $this->bitrixCurrentUserId();
        if (!$userId) {
            $result->addError(new \Bitrix\Main\Error("Authenticated userId cannot be determined"));
            return $result;
        }

        $result->setData([
            'userId' => $userId,
            'authType' => $type,
            'email' => $userDto?->getEmail() ?? null,
        ]);

        return $result;
    }

    /**
     * Регистрация пользователя через выбранный аутентификатор.
     *
     * @return Result{userId:int, authType:string, email:string|null}
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function register(string $type, AuthCredentialsDto $userDto): Result
    {
        $result = new Result();
        $authenticator = $this->authenticators[$type] ?? null;

        if (!$authenticator) {
            $result->addError(new \Bitrix\Main\Error("Unknown auth type: {$type}"));
            return $result;
        }

        $validator = $this->validators[$type];
        $validationResult = $validator->validateForRegistration($userDto);
        if (!$validationResult->isSuccess()) {
            $result->addErrors($validationResult->getErrors());
        }

        $authenticator->register($userDto);

        $userId = $this->bitrixCurrentUserId();
        if (!$userId) {
            $result->addError(new \Bitrix\Main\Error("Registered userId cannot be determined"));
            return $result;
        }

        $result->setData([
            'userId' => $userId,
            'authType' => $type,
            'email' => $userDto->getEmail(),
        ]);
        return $result;
    }

    /**
     * Получение URL или HTML для авторизации через внешний сервис
     * 
     * @param string $type Тип авторизации
     * @return array{type:string, value:string}
     * @throws \RuntimeException
     */
    public function getAuthorizationUrlOrHtml(string $type): array
    {
        if (!isset($this->authenticators[$type])) {
            throw new \RuntimeException("Unknown authenticator type: {$type}");
        }
        return $this->authenticators[$type]->getAuthorizationUrlOrHtml() ?? [];
    }

    /**
     * Список доступных методов аутентификации.
     * 
     * @return string[]
     */
    public function getAvailable(): array
    {
        return array_keys($this->authenticators);
    }

    // -------------------- Вспомогательное --------------------

    /**
     * Получение ID текущего авторизованного пользователя из Bitrix
     * 
     * @return int|null
     */
    protected function bitrixCurrentUserId(): ?int
    {
        $user = service(UserRepositoryContract::class)->getCurrentUser();
        if ($user->isAuthorized()) {
            return $user->getId();
        }
        return null;
    }
}
