<?php

namespace Beeralex\Notification\Events;

use Beeralex\Notification\Options;

class EventHandlers
{
    public static function mainOnBeforeEventAdd(
        string &$event,
        string &$lid,
        array &$arFields,
        ?string &$message_id,
        ?array &$files,
        ?string $languageId
    ) {
        return (new MailEventInterceptor(service(Options::class)->moduleEnable))->handle($event, $lid, $arFields, $message_id, $files, $languageId);
    }

    public static function mainOnBeforeSendSms(\Bitrix\Main\Event $event)
    {
        $params = $event->getParameters();
        $fields = $params['fields'];
        if (empty($fields)) {
            return new \Bitrix\Main\Result(); // пока не знаю как обработать отсутствие полей
        }
        $eventName = $params['template']['EVENT_NAME'] ?? null;
        return (new SmsEventInterceptor(service(Options::class)->moduleEnable))->handle($eventName, $fields);
    }
}
