<?

namespace Beeralex\Notification\Channels;

use Bitrix\Main\Mail\Event;
use Beeralex\Notification\Contracts\NotificationChannelContract;
use Beeralex\Notification\Dto\NotificationMessage;
use Beeralex\Notification\Enum\Channel;

class EmailChannel implements NotificationChannelContract
{
    public function send(NotificationMessage $message): \Bitrix\Main\Result
    {
        return Event::send([
            'EVENT_NAME' => $message->eventName,
            'LID' => $message->lid ?? SITE_ID ?: 's1',
            'C_FIELDS' => $message->fields,
            'MESSAGE_ID' => $message->messageId,
            'FILES' => $message->files,
            'LANGUAGE_ID' => $message->languageId,
        ]);
    }

    public static function getDisplayName(): string
    {
        return 'Email уведомления';
    }

    public static function getCode(): string
    {
        return Channel::EMAIL->value;
    }
}
