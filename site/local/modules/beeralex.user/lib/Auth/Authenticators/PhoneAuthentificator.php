<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Authenticators;

use Beeralex\User\Auth\Contracts\PhoneAuthentificatorContract;
use Beeralex\User\Contracts\UserRepositoryContract;
use Beeralex\User\Dto\BaseUserDto;
use Beeralex\User\Phone;
use Beeralex\User\Auth\PhoneCodeService;

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

    public function authenticate(?BaseUserDto $data = null): void
    {
        if ($data === null || !$data->phone) {
            throw new \InvalidArgumentException('Phone number must be provided');
        }

        $this->authenticateByPhone(Phone::fromString($data->phone, $data->codeVerify));
    }

    public function authenticateByPhone(Phone $phone, ?string $code = null): void
    {
        if ($code === null) {
            $result = $this->codeService->sendCode($phone);
            if ($result) {
                return;
            }

            throw new \RuntimeException('Verification code sent to your phone');
        }

        $userId = $this->codeService->verifyCode($phone, (string)$code);
        if (!$userId) {
            throw new \RuntimeException('Invalid verification code');
        }

        $this->authorizeByUserId($userId);
        // $this->authorizeByUserId($user->getId());
    }
}
