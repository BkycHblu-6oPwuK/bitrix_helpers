<?php

declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock\Content;

use Beeralex\Core\Repository\IblockRepository;
use Beeralex\Core\Service\IblockService;

class MainRepository extends IblockRepository
{
    public function __construct(string|int $iblockCodeOrId)
    {
        return parent::__construct($iblockCodeOrId);
    }

    public function getContent(): array
    {
        $query = service(IblockService::class)->addSectionModelToQuery($this->entityId, $this->query())
            ->where('ACTIVE', 'Y')
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
                    'ARTICLES_TYPE.ITEM.XML_ID',
                    'MAIN_BANNER.VALUE',
                    'IBLOCK_MODEL_SECTION.CODE',
                    'VIDEO_IDS.VALUE',
                    'PRODUCTS_SECTION_IDS.VALUE',
                ]
            )
            ->setOrder(['SORT' => 'ASC']);
        return $this->queryService->fetchGroupedEntities($query);
    }
}
