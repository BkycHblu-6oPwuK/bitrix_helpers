<?

namespace Beeralex\Notification\Contracts;

use Beeralex\Core\Repository\RepositoryContract;

interface EventTypeRepositoryContract extends RepositoryContract
{
    public function getByLanguage(string $language = 'ru', array $select = ['*'], array $order = []): array;
}
