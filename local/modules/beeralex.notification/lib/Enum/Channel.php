<?php
namespace Beeralex\Notification\Enum;

enum Channel: string
{
    case EMAIL = 'email';
    case SMS = 'sms';
    case TELEGRAM = 'telegram';
    case PUSH = 'push';
}