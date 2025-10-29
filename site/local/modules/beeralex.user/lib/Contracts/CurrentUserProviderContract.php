<?php

namespace Beeralex\User\Contracts;

interface CurrentUserProviderContract
{
    public function getCurrent(): UserEntityContract;
}