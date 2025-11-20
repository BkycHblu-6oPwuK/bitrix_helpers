<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Authenticators;

use Beeralex\User\Auth\Contracts\EmailAuthenticatorContract;
use Beeralex\User\Dto\AuthCredentialsDto;
use Beeralex\User\Exceptions\IncorrectOldPasswordException;
use Beeralex\User\Exceptions\UserNotFoundException;
use Beeralex\User\Validation\Validator\EmailRegisterValidator;
use Bitrix\Main\Result;
use Bitrix\Main\Security\Password;

class EmailAuthenticator extends BaseAuthentificator implements EmailAuthenticatorContract
{
    public function getKey(): string
    {
        return 'email';
    }

    public function getTitle(): string
    {
        return 'Авторизация по E-mail';
    }

    public function authenticate(?AuthCredentialsDto $data = null): Result
    {
        if ($data === null) {
            $result = new Result();
            $result->addError(new \Bitrix\Main\Error("data must be provided for email authentication"));
            return $result;
        }
        // if($validator = $this->getValidator()) {
        //     $validationResult = $validator->validate($data);
        //     if (!$validationResult->isSuccess()) {
        //         $result = new Result();
        //         foreach ($validationResult->getErrors() as $error) {
        //             $result->addError($error);
        //         }
        //         return $result;
        //     }
        // }
        return $this->authenticateByEmail(
            $data->getEmail(),
            $data->getPassword()
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

    /**
     * @return EmailRegisterValidator
     */
    protected function getValidator() : ?\Bitrix\Main\Validation\Validator\ValidatorInterface
    {
        return new EmailRegisterValidator();
    }
}
