<?php

use Beeralex\Api\Domain\Iblock\Content\ContentRepository;
use Bitrix\Main\Application;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @example usage:
 * $APPLICATION->IncludeComponent(
 *       "beeralex:content",
 *       ".default",
 *       [
 *           'CODE' => 'achievements',
 *           'CACHE_TIME' => 3600000,
 *           'CACHE_TYPE' => 'A',
 *       ],
 *       false
 *   );
 */
class BeeralexContent extends \CBitrixComponent
{
    protected ContentRepository $contentRepository;

    public function onPrepareComponentParams($params)
    {
        $this->contentRepository = service(ContentRepository::class);
        return $params;
    }

    public function executeComponent()
    {
        if (empty($this->arParams['CODE'])) {
            throw new \InvalidArgumentException('CODE parameter is required');
        }

        $taggedCache = Application::getInstance()->getTaggedCache();
        $iblockId = (int)$this->contentRepository->entityId;

        if ($this->startResultCache(false, $this->arParams['CODE'])) {
            try {
                $taggedCache->startTagCache($this->getCachePath());

                $taggedCache->registerTag('iblock_id_' . $iblockId);
                $taggedCache->registerTag('beeralex_content_' . $this->arParams['CODE']);

                $this->arResult = $this->contentRepository->getContent(
                    $this->arParams['CODE']
                );

                $taggedCache->endTagCache();

                $this->endResultCache();
            } catch (\Throwable $e) {
                $taggedCache->abortTagCache();
                $this->abortResultCache();
                throw $e;
            }
        }

        $this->includeComponentTemplate();
    }
}
