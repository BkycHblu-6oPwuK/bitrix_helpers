<?

namespace App\Http\Controllers\User;

use App\User\Auth\Dto\EmailRegisterRequestDto;
use Beeralex\Core\Http\Controllers\ApiController;

class UserController extends ApiController
{
    public function configureActions()
    {
        return [
            'register' => [
                'prefilters' => [],
            ],
        ];
    }
    
    public function registerAction(
        EmailRegisterRequestDto $dto)
    {
        dd($dto);
    }
}
