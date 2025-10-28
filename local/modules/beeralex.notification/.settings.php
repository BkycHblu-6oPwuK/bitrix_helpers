<?php

use Beeralex\Notification\Contracts\EventTypeRepositoryContract;
use Beeralex\Notification\Contracts\NotificationChannelRepositoryContract;
use Beeralex\Notification\Contracts\NotificationCodeRepositoryContract;
use Beeralex\Notification\Contracts\NotificationLinkEventTypeRepositoryContract;
use Beeralex\Notification\Contracts\NotificationsRepositoryContract;
use Beeralex\Notification\Contracts\NotificationTemplateLinkRepositoryContract;
use Beeralex\Notification\Contracts\NotificationTypeRepositoryContract;
use Beeralex\Notification\Contracts\UserNotificationPreferenceRepositoryContract;
use Beeralex\Notification\Repository\EventTypeRepository;
use Beeralex\Notification\Repository\NotificationChannelRepository;
use Beeralex\Notification\Repository\NotificationCodeRepository;
use Beeralex\Notification\Repository\NotificationLinkEventTypeRepository;
use Beeralex\Notification\Repository\NotificationsRepository;
use Beeralex\Notification\Repository\NotificationTemplateLinkRepository;
use Beeralex\Notification\Repository\NotificationTypeRepository;
use Beeralex\Notification\Repository\UserNotificationPreferenceRepository;

return [
    'services' => [
        'value' => [
            NotificationChannelRepositoryContract::class => [
                'className' => NotificationChannelRepository::class,
            ],
            NotificationCodeRepositoryContract::class => [
                'className' => NotificationCodeRepository::class,
            ],
            NotificationLinkEventTypeRepositoryContract::class => [
                'className' => NotificationLinkEventTypeRepository::class,
            ],
            NotificationsRepositoryContract::class => [
                'className' => NotificationsRepository::class,
            ],
            NotificationTypeRepositoryContract::class => [
                'className' => NotificationTypeRepository::class,
            ],
            UserNotificationPreferenceRepositoryContract::class => [
                'className' => UserNotificationPreferenceRepository::class,
            ],
            NotificationTemplateLinkRepositoryContract::class => [
                'className' => NotificationTemplateLinkRepository::class,
            ],
            EventTypeRepositoryContract::class => [
                'className' => EventTypeRepository::class,
            ],
        ],
    ],
];
