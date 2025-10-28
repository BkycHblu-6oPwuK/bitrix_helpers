<?
namespace Beeralex\Notification\Dto;

class NotificationMessage
{
    public function __construct(
        public string $eventName,
        public array $fields,
        public int $userId,
        public ?string $lid = null,
        public ?string $messageId = null,
        public ?array $files = null,
        public ?string $languageId = null
    ) {}
}