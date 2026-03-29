<?php

declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\ActionFilter\AuthFilter;
use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Api\ApiResult;
use Beeralex\Api\Domain\User\UserService;
use Beeralex\Core\Http\Controllers\ApiController;

class UserController extends ApiController
{
    use ApiProcessResultTrait;

    protected UserService $userService;

    protected function init(): void
    {
        parent::init();
        $this->userService = \service(UserService::class);
    }

    public function configureActions(): array
    {
        return [
            'me' => [
                'prefilters' => [
                    new AuthFilter(),
                ],
            ],
        ];
    }

    public function meAction(): array
    {
        return $this->process(function () {
            $result = \service(ApiResult::class);
            $userDTO = $this->userService->getCurrentUserDTO();
            $result->setData([
                'user' => $userDTO
            ]);
            return $result;
        });
    }
}
