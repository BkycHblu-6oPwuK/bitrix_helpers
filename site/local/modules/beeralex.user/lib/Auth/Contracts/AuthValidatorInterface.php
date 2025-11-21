<?

namespace Beeralex\User\Auth\Contracts;

use Beeralex\User\Auth\AuthCredentialsDto;

interface AuthValidatorInterface
{
    /**
     * Валидация данных для регистрации.
     */
    public function validateForRegistration(AuthCredentialsDto $credentials): \Bitrix\Main\Validation\ValidationResult;

    /**
     * Валидация данных для входа.
     */
    public function validateForLogin(AuthCredentialsDto $credentials): \Bitrix\Main\Validation\ValidationResult;
}
