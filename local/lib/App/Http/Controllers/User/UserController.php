<?

namespace App\Http\Controllers\User;

use Beeralex\Core\Http\Controllers\ApiController;
use Beeralex\User\Auth\Authenticators\EmailAuthenticator;
use Beeralex\User\Auth\Authenticators\TelegramAuthenticator;
use Beeralex\User\Auth\AuthManager;
use Beeralex\User\Auth\Dto\EmailRegisterRequestDto;
use Beeralex\User\Auth\Dto\TelegramAuthRequestDto;

class UserController extends ApiController
{
    protected readonly AuthManager $manager;
    public function __construct(?\Bitrix\Main\Request $request = null)
    {
        parent::__construct($request);
        $this->manager = service(AuthManager::class);
    }

    public function configureActions()
    {
        return [
            'register' => [
                'prefilters' => [],
            ],
            'authByTelegram' => [
                'prefilters' => [],
            ],
        ];
    }
    
    public function registerAction(
        EmailRegisterRequestDto $dto)
    {
        $this->manager->register(EmailAuthenticator::getKey(), $dto);
    }

    public function authByTelegramAction(
        TelegramAuthRequestDto $dto)
    {
        $this->manager->attempt(TelegramAuthenticator::getKey(), $dto);
    }
}
