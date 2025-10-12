<?php
namespace App\Main\Request;

use Bitrix\Main\Context;
use Bitrix\Main\Web\Json;

class RequestResolver
{
    public static function resolve(string $dtoClass): AbstractRequestDto
    {
        if (!is_subclass_of($dtoClass, AbstractRequestDto::class)) {
            throw new \InvalidArgumentException("$dtoClass must implement AbstractRequestDto");
        }

        $request = Context::getCurrent()->getRequest();
        $data = array_merge($request->getQueryList()->toArray(), $request->getPostList()->toArray());

        if ($json = Json::decode($request->getInput())) {
            $data = array_merge($data, $json);
        }

        /** @var AbstractRequestDto $dto */
        $dto = new $dtoClass();
        $dto->fromArray($data);
        $dto->validate();

        return $dto;
    }
}