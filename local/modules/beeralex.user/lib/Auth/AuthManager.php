<?

namespace Beeralex\User\Auth;

use Beeralex\User\Auth\Contracts\AuthenticatorContract;
use Beeralex\User\Dto\BaseUserDto;

class AuthManager
{
    /**
     * ключом выступает название интерфейса
     * @param AuthenticatorContract[] $authenticators
     */
    public function __construct(public readonly array $authenticators) {}

    /**
     * @param string|\Beeralex\User\Auth\Authenticators\BaseAuthentificator $type
     * @throws \Exception
     */
    public function attempt(string $type, ?BaseUserDto $userDto = null)
    {
        $type = SocialAuthenticatorFactory::formatKey($type);
        $authenticator = $this->authenticators[$type] ?? null;

        if (!$authenticator) {
            throw new \InvalidArgumentException("Unknown auth type: {$type}");
        }
        if(!$authenticator->isService() && $userDto === null) {
            throw new \InvalidArgumentException("User data must be provided for local authenticators");
        }

        $authenticator->authenticate($userDto);
    }

    /**
     * @param string|\Beeralex\User\Auth\Authenticators\BaseAuthentificator $type
     * @throws \Exception
     */
    public function register(string $type, BaseUserDto $userDto): void
    {
        $type = SocialAuthenticatorFactory::formatKey($type);
        if (!isset($this->authenticators[$type])) {
            throw new \RuntimeException("Unknown authenticator type: {$type}");
        }

        $this->authenticators[$type]->register($userDto);
    }

    public function getAuthorizationUrl(string $type): string
    {
        $type = SocialAuthenticatorFactory::formatKey($type);
        if (!isset($this->authenticators[$type])) {
            throw new \RuntimeException("Unknown authenticator type: {$type}");
        }

        return $this->authenticators[$type]->getAuthorizationUrl();
    }
}
