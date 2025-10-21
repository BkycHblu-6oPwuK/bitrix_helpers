<?
namespace Beeralex\Notification\Contracts;

use Beeralex\Notification\Dto\NotificationMessage;

interface NotificationChannelContract
{
    public function send(NotificationMessage $message): bool;
}
