<?php
declare(strict_types=1);
namespace Beeralex\User\Contracts;

interface CurrentUserProviderContract
{
    public function getCurrent(): UserEntityContract;
}