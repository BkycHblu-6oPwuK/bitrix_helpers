<?

namespace App\Http\Controllers\User;

use App\User\Auth\Authenticators\EmailAuthenticator;
use App\User\Auth\Authenticators\TelegramAuthenticator;
use App\User\Auth\AuthManager;
use App\User\Auth\Dto\EmailRegisterRequestDto;
use App\User\Auth\Dto\TelegramAuthRequestDto;
use Beeralex\Core\Http\Controllers\ApiController;
use Bitrix\Main\DI\ServiceLocator;

class UserController extends ApiController
{
    protected readonly AuthManager $manager;
    public function __construct(?\Bitrix\Main\Request $request = null)
    {
        parent::__construct($request);
        $this->manager = ServiceLocator::getInstance()->get(AuthManager::class);
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
