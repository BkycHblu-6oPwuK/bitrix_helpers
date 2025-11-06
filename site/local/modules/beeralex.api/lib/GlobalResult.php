<?php

namespace Beeralex\Api;

class GlobalResult
{
    public static array $result = [];

    public static function addPageData(array $data, ?string $key = null)
    {
        if ($key) {
            static::$result['page'][$key] = $data;
        } else {
            static::$result['page'][] = $data;
        }
    }

    public static function setEmptyPageData()
    {
        if(empty(static::$result['page'])){
            static::$result['page'] = [];
        }
    }

    public static function setSeo(array $data = [])
    {
        global $APPLICATION;
        static::$result['seo'] = array_merge(
            [
                'title' => $APPLICATION->GetPageProperty('title') ?: '',
                'description' => $APPLICATION->GetPageProperty('description') ?: '',
            ],
            $data
        );
    }
}
