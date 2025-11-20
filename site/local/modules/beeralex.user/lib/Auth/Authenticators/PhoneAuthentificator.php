<?php

declare(strict_types=1);

namespace Beeralex\User\Auth\Authenticators;

use Beeralex\User\Auth\Contracts\PhoneAuthentificatorContract;
use Beeralex\User\Contracts\UserRepositoryContract;
use Beeralex\User\Dto\AuthCredentialsDto;
use Beeralex\User\Phone;
use Beeralex\User\Auth\PhoneCodeService;
use Bitrix\Main\Result;
use Beeralex\User\Validation\Validator\PhoneRegisterValidator;

class PhoneAuthentificator extends BaseAuthentificator implements PhoneAuthentificatorContract
{
    public function __construct(
        protected readonly PhoneCodeService $codeService,
        protected readonly UserRepositoryContract $userRepository,
    ) {}

    public function getKey(): string
    {
        return 'phone';
    }

    public function getTitle(): string
    {
        return 'Авторизация по номеру телефона';
    }

    public function authenticate(?AuthCredentialsDto $data = null): Result
    {
        if ($data === null || !$data->getPhone()) {
            $result = new Result();
            $result->addError(new \Bitrix\Main\Error('Phone number must be provided'));
            return $result;
        }

        if($validator = $this->getValidator()) {
            $validationResult = $validator->validate($data);
            if (!$validationResult->isSuccess()) {
                $result = new Result();
                foreach ($validationResult->getErrors() as $error) {
                    $result->addError($error);
                }
                return $result;
            }
        }

        return $this->authenticateByPhone(
            Phone::fromString($data->getPhone()),
            $data->getCodeVerify()
        );
    }

    public function authenticateByPhone(Phone $phone, ?string $code = null): Result
    {
        $result = new Result();
        if ($code === null) {
            $resultSendCode = $this->codeService->sendCode($phone);
            if ($resultSendCode) {
                return $result;
            }

            $result->addError(new \Bitrix\Main\Error('Verification code sent to your phone'));
            return $result;
        }

        $userId = $this->codeService->verifyCode($phone, (string)$code);
        if (!$userId) {
            $result->addError(new \Bitrix\Main\Error('Invalid verification code'));
            return $result;
        }

        $this->authorizeByUserId($userId);
        // $this->authorizeByUserId($user->getId());
        return $result;
    }

    /**
     * @return PhoneRegisterValidator
     */
    protected function getValidator(): ?\Bitrix\Main\Validation\Validator\ValidatorInterface
    {
        return new PhoneRegisterValidator();
    }
}
