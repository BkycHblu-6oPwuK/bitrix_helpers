<?
namespace Beeralex\Notification;

class ChannelFactory
{
    /**
     * Создает экземпляр канала по его коду
     */
    public static function createChannel(string $code): ?Contracts\NotificationChannelContract
    {
        $channels = ChannelRegistry::getAvailableChannels();
        foreach ($channels as $class) {
            if (is_subclass_of($class, Contracts\NotificationChannelContract::class) && $class::getCode() === $code) {
                return new $class;
            }
        }

        return null;
    }
}
