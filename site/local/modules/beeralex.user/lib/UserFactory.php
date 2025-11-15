<?

declare(strict_types=1);

namespace Beeralex\User;

use Beeralex\User\Contracts\UserEntityContract;
use Beeralex\User\Contracts\UserFactoryContract;

class UserFactory implements UserFactoryContract
{
    public function create(array $fields): UserEntityContract
    {
        return new User($fields);
    }
}
