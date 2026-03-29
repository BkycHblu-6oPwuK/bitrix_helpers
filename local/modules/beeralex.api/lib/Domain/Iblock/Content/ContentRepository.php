<?php

declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock\Content;

use Beeralex\Core\Repository\IblockRepository;
use Beeralex\Core\Service\FileService;

class ContentRepository extends IblockRepository
{
    public function __construct(string|int $iblockCodeOrId, protected readonly FileService $fileService)
    {
        return parent::__construct($iblockCodeOrId);
    }

    public function getContent(string $code): array
    {
        $query = $this->fileService->addPictireSrcInQuery($this->query(), 'PREVIEW_PICTURE')
            ->where('ACTIVE', 'Y')
            ->where('CODE', $code)
            ->setSelect(
                [
                    'ID',
                    'NAME',
                    'PREVIEW_TEXT',
                    'PICTURE_SRC',
                ]
            )
            ->setOrder(['SORT' => 'ASC']);
        $result = $this->queryService->fetchGroupedEntities($query);
        return $result[0] ?? [];
    }
}
