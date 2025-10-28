<?
namespace Beeralex\Notification;

class NotificationLock
{
    protected static array $locks = [];

    public static function lock(string $channel): void
    {
        self::$locks[$channel] = true;
    }

    public static function unlock(string $channel): void
    {
        unset(self::$locks[$channel]);
    }

    public static function isLocked(string $channel): bool
    {
        return isset(self::$locks[$channel]);
    }
}
