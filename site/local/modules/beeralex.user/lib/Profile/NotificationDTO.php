<?php

namespace Beeralex\User\Profile;

class NotificationDTO
{
    public string $type = '';
    public string $name = '';
    /**
     * @var ChannelDTO[] $notifications
     */
    public array $channels = [];
}
