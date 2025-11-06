<?php

namespace Beeralex\User\Profile;

use Beeralex\Core\Config\Config;
use Beeralex\User\User;

class PersonalBuilder
{
    protected array $notificationsResult;
    /**
     * @param array $notificationsResult из NotificationPreferenceService::getAll
     */
    public function __construct(array $notificationsResult)
    {
        $this->notificationsResult = $notificationsResult;
    }

    public function build(): PersonalDTO
    {
        $user = User::current();
        $dto = new PersonalDTO;
        $dto->name = $user->getName();
        $dto->lastName = $user->getLastName();
        $dto->phone = $user->getPhone()?->getFormatted() ?? '';
        $dto->email = $user->getEmail();
        $dto->birthday = $user->getBirthday()?->format(Config::getInstance()->dateFormatSite) ?: '';
        $dto->photo = $user->getPhoto();
        $dto->setGender($user->getGender());
        $dto->notifications = $this->buildNotifications();
        return $dto;
    }

    protected function buildNotifications(): array
    {
        $result = [];

        foreach ($this->notificationsResult as $notification) {
            $notificationDto = new NotificationDTO;
            $notificationDto->type = $notification['type']?->value;
            $notificationDto->name = $notification['name'];

            //$hasEnabledChannel = false;
            //$emailChannelDto = null;

            foreach ($notification['channels'] as $channel) {
                $channelDto = new ChannelDTO;
                $channelDto->name = $channel['name'];
                $channelDto->type = $channel['channel']?->value;
                $channelDto->isEnable = $channel['enabled'];

                //if ($channelDto->isEnable) {
                //    $hasEnabledChannel = true;
                //}
                //if ($channel['channel'] === Channels::EMAIL) {
                //    $emailChannelDto = $channelDto;
                //}

                $notificationDto->channels[] = $channelDto;
            }

            //if (!$hasEnabledChannel && $emailChannelDto) {
            //    $emailChannelDto->isEnable = true;
            //}

            $result[] = $notificationDto;
        }

        return $result;
    }
}
