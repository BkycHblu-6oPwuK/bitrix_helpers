<?

namespace App\User\Auth;

use App\User\Auth\Contracts\AuthenticatorContract;
use App\User\User;

class AuthManager
{
    /**
     * @param AuthenticatorContract[] $authenticators
     */
    public function __construct(private array $authenticators) {}

    public function attempt(string $type, array $credentials): ?User
    {
        $authenticator = $this->authenticators[$type] ?? null;

        if (!$authenticator) {
            throw new \InvalidArgumentException("Unknown auth type: {$type}");
        }

        return $authenticator->authenticate($credentials);
    }

    public function register(string $type, array $data): ?User
    {
        if (!isset($this->authenticators[$type])) {
            throw new \RuntimeException("Unknown authenticator type: {$type}");
        }

        return $this->authenticators[$type]->register($data);
    }
}
