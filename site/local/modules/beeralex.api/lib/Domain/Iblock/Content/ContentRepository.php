<?php

declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock\Content;

use Beeralex\Core\Repository\IblockRepository;
use Beeralex\Core\Service\IblockService;
use Beeralex\Core\Service\QueryService;

class ContentRepository extends IblockRepository
{
    public function __construct(string|int $iblockCodeOrId)
    {
        return parent::__construct($iblockCodeOrId);
    }

    public function getContentByPath(string $path): array
    {
        $query = service(IblockService::class)->addSectionModelToQuery($this->entityId, $this->query())
            ->where('ACTIVE', 'Y')
            ->where('IBLOCK_MODEL_SECTION.UF_URL', $path)
            ->setSelect(
                [
                    'ID',
                    'NAME',
                    'PRODUCTS_TITLE.VALUE',
                    'PRODUCTS_IDS.VALUE',
                    'PRODUCTS_TYPE.ITEM.XML_ID',
                    'TYPE.ITEM.XML_ID',
                    'LINK.VALUE',
                    'ARTICLES_IDS.VALUE',
                    'ARTICLES_TITLE.VALUE',
                    'MAIN_BANNER.VALUE',
                    'IBLOCK_MODEL_SECTION.UF_URL',
                    'FORM_ID.VALUE',
                    'HTML.VALUE',
                    'VIDEO_IDS.VALUE'
                ]
            )
            ->setOrder(['SORT' => 'ASC']);
        return $this->queryService->fetchGroupedEntities($query);
    }
}
