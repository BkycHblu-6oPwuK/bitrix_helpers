<?

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\User\Auth\Dto\EmailRegisterRequestDto;

class UserController extends BaseController
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
