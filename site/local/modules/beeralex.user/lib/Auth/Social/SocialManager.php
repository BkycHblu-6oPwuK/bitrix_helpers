<?
declare(strict_types=1);
namespace Beeralex\User\Auth\Social;

use Beeralex\User\Auth\Social\BitrixSocialServiceAdapter;

class SocialManager
{
    /**
     * ключом выступает название интерфейса
     * @param BitrixSocialServiceAdapter[] $adapters
     */
    public function __construct(public readonly array $adapters) {}

    /**
     * @param string|\Beeralex\User\Auth\Authenticators\AbstractAuthentificator $type
     * @throws \Exception
     */
    public function get(string $key) : BitrixSocialServiceAdapter
    {
        $adapter = $this->adapters[$key] ?? null;
        if (!$adapter) {
            throw new \InvalidArgumentException("Unknown social service adapter: {$key}");
        }
        return $adapter;
    }
}
