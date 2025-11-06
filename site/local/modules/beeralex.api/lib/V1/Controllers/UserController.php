<?

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Core\Http\Controllers\ApiController;
use Beeralex\User\Auth\AuthManager;

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

        ];
    }
}
