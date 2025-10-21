<?
namespace Beeralex\Notification\Dto;

class NotificationMessage
{
    public function __construct(
        public string $eventName,
        public array $fields,
        public int $userId
    ) {}
}