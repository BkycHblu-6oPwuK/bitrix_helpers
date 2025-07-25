<?php

namespace Itb\User\Profile;

class DressingBuilder extends OrdersBuilder
{
    protected function setOrdersIds(): void 
    {
        $this->ordersIds = collect($this->ordersRepository->getDressingOrdersIdsByUser($this->user->getId()));
    }
}
