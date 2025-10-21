<?
namespace Beeralex\Notification\Channels;

use Bitrix\Main\Mail\Event;
use Beeralex\Notification\Contracts\NotificationChannelContract;
use Beeralex\Notification\Dto\NotificationMessage;

class EmailChannel implements NotificationChannelContract
{
    public function send(NotificationMessage $message): bool
    {
        return Event::send([
            'EVENT_NAME' => $message->eventName,
            'LID' => SITE_ID ?: 's1',
            'C_FIELDS' => $message->fields,
        ])->isSuccess();
    }
}