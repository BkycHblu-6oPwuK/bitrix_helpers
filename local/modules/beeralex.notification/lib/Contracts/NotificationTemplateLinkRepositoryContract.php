<?
namespace Beeralex\Notification\Contracts;

use Beeralex\Core\Repository\RepositoryContract;

interface NotificationTemplateLinkRepositoryContract extends RepositoryContract
{
    public function activate(int $id): void;
    public function deactivate(int $id): void;
}
