<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Authenticators;

use Beeralex\User\Auth\Contracts\EmailAuthenticatorContract;
use Beeralex\User\Auth\AuthCredentialsDto;
use Beeralex\User\Validation\Validator\EmailRegisterValidator;
use Bitrix\Main\Result;
use Bitrix\Main\Security\Password;

class EmailAuthenticator extends AbstractAuthentificator implements EmailAuthenticatorContract
{
    public function getKey(): string
    {
        return 'email';
    }

    public function getTitle(): string
    {
        return 'Авторизация по E-mail';
    }

    public function authenticate(AuthCredentialsDto $credentials): Result
    {
        if ($credentials->isEmpty()) {
            $result = new Result();
            $result->addError(new \Bitrix\Main\Error("data must be provided for email authentication"));
            return $result;
        }
        return $this->authenticateByEmail(
            $credentials->getEmail(),
            $credentials->getPassword()
        );
    }

    public function authenticateByEmail(string $email, string $password): Result
    {
        $result = new Result();
        $user = $this->userRepository->getByEmail($email);
        if (!$user) {
            $result->addError(new \Bitrix\Main\Error("User with email {$email} not found"));
            return $result;
        }

        if (!Password::equals($user->getPassword(), $password)) {
            $result->addError(new \Bitrix\Main\Error("Incorrect password for email {$email}"));
            return $result;
        }

        $this->authorizeByUserId($user->getId());
        return $result;
    }
}
