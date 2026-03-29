<?

namespace Beeralex\Notification\Contracts;

use Beeralex\Notification\Dto\NotificationMessage;

interface NotificationChannelContract
{
    /**
     * Отправляет уведомление через канал
     */
    public function send(NotificationMessage $message): \Bitrix\Main\Result;

    /**
     * Возвращает человекопонятное название канала для админки
     */
    public static function getDisplayName(): string;

    /**
     * Возвращает уникальный код канала
     */
    public static function getCode(): string;
}
