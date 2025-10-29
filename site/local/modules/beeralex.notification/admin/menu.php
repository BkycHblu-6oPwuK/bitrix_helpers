<?php
$aMenu = [
    [
        "parent_menu" => "global_menu_services",
        "section" => "beeralex_notification",
        "sort" => 210,
        "text" => "Уведомления",
        "title" => "Управление уведомлениями",
        "icon" => "mail_menu_icon",
        "page_icon" => "mail_page_icon",
        "items_id" => "menu_beeralex_notification",
        "items" => [
            [
                "text" => "Каналы уведомлений",
                "url" => "/bitrix/admin/beeralex_notification_channels.php",
                "more_url" => ["beeralex_notification_channel.php"],
                "title" => "Email, sms, telegram",
            ],
            [
                "text" => "Типы уведомлений",
                "url" => "/bitrix/admin/beeralex_notification_types.php",
                "more_url" => ["beeralex_notification_type.php"],
                "title" => "Группы уведомлений (заказы, пользователи и т.д.)",
            ],
            [
                "text" => "Связи с событиями Bitrix",
                "url" => "/bitrix/admin/beeralex_notification_link_events.php",
                "more_url" => ["beeralex_notification_link_event.php"],
                "title" => "Привязка типов уведомлений к событиям Bitrix",
            ],
            [
                "text" => "Связи шаблонов",
                "url" => "/bitrix/admin/beeralex_notification_template_links.php",
                "more_url" => ["beeralex_notification_template_link.php"],
                "title" => "Привязка шаблонов смс к каналам",
            ],
        ],
    ],
];

return $aMenu;
