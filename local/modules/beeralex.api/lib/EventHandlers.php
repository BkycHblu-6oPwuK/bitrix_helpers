<?php

namespace Beeralex\Api;

use Beeralex\Api\Domain\EventHandlers\CouponHandler;
use Beeralex\User\Auth\JwtTokenHandler;
use Beeralex\Api\Domain\Services\CacheInvalidateService;
use Beeralex\Core\Service\IblockService;
use Bitrix\Main\Application;

class EventHandlers
{
    public static function onPageStart()
    {
        $options = service(Options::class);
        if (!$options->spaApiEnabled) {
            return;
        }

        $app = Application::getInstance();
        $request = $app->getContext()->getRequest();

        JwtTokenHandler::handle($request); // авторизация по JWT токену
        CouponHandler::handle($request); // обработка купона из заголовка
    }

    /**
     * Добавляет тип кеша в очередь на сброс.
     */
    private static function addToQueue(string $type): void
    {
        $GLOBALS['NUXT_CACHE_INVALIDATION_TYPES'][$type] = true;
    }

    /**
     * Определяет группу кеша по ID инфоблока.
     */
    private static function getCacheType(int $iblockId): string
    {
        $iblockService = service(IblockService::class);
        if (in_array($iblockId, [$iblockService->getIblockIdByCode('catalog'), $iblockService->getIblockIdByCode('offers')], true)) {
            return 'catalog';
        }

        if (in_array($iblockId, [$iblockService->getIblockIdByCode('articles')], true)) {
            return 'articles';
        }

        return 'other';
    }

    /**
     * Обработчик изменения элемента инфоблока.
     */
    public static function onElementChange(array $fields): void
    {
        if (!empty($fields['IBLOCK_ID'])) {
            static::addToQueue(
                static::getCacheType((int) $fields['IBLOCK_ID'])
            );
        }
    }

    /**
     * Обработчик изменения раздела инфоблока.
     */
    public static function onSectionChange(array $fields): void
    {
        if (!empty($fields['IBLOCK_ID'])) {
            static::addToQueue(
                static::getCacheType((int) $fields['IBLOCK_ID'])
            );
        }
    }

    /**
     * Выполняет сброс накопленных типов кеша.
     */
    public static function onEndBufferContent(&$content): void
    {
        $types = array_keys(
            $GLOBALS['NUXT_CACHE_INVALIDATION_TYPES'] ?? []
        );

        if (!$types) {
            return;
        }

        $GLOBALS['NUXT_CACHE_INVALIDATION_TYPES'] = [];

        foreach ($types as $type) {
            service(CacheInvalidateService::class)->invalidateCache($type);
        }
    }
}
