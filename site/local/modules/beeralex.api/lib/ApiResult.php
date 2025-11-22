<?php
declare(strict_types=1);

namespace Beeralex\Api;

/**
 * Синглтон если получаете из DI контейнера, для формирования результатов API с дополнительными методами.
 */
class ApiResult extends \Bitrix\Main\Result
{
    public function addPageData(array $data, ?string $key = null)
    {
        if ($key) {
            $this->data['page'][$key] = $data;
        } else {
            $this->data['page'][] = $data;
        }
    }

    public function setEmptyPageData()
    {
        if(empty($this->data['page'])){
            $this->data['page'] = [];
        }
    }

    public function setSeo(array $data = [])
    {
        global $APPLICATION;
        $this->data['seo'] = array_merge(
            [
                'title' => $APPLICATION->GetPageProperty('title') ?: '',
                'description' => $APPLICATION->GetPageProperty('description') ?: '',
            ],
            $data
        );
    }
}
