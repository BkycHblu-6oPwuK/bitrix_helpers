<?

namespace App\User\Auth;

use App\User\Auth\Contracts\AuthenticatorContract;
use App\User\Dto\BaseUserDto;

class AuthManager
{
    /**
     * ключом выступает название интерфейса
     * @param AuthenticatorContract[] $authenticators
     */
    public function __construct(protected array $authenticators) {}

    /**
     * @param string|\App\User\Auth\Authenticators\BaseAuthentificator $type
     * @throws \Exception
     */
    public function attempt(string $type, BaseUserDto $userDto)
    {
        $authenticator = $this->authenticators[$type] ?? null;

        if (!$authenticator) {
            throw new \InvalidArgumentException("Unknown auth type: {$type}");
        }

        $authenticator->authenticate($userDto);
    }

    /**
     * @param string|\App\User\Auth\Authenticators\BaseAuthentificator $type
     * @throws \Exception
     */
    public function register(string $type, BaseUserDto $userDto): void
    {
        if (!isset($this->authenticators[$type])) {
            throw new \RuntimeException("Unknown authenticator type: {$type}");
        }

        $this->authenticators[$type]->register($userDto);
    }
}
