<?
use Beeralex\Api\Domain\Pagination\PaginationDTO;
use Beeralex\Core\Service\FileService;

?>

<div class="section__container">
    <div class="favourites-block">
        <div class="favourites-block__container">
            <?

            foreach ($arResult['items'] as $item) {
                $APPLICATION->IncludeComponent(
                    'bitrix:catalog.item',
                    'main-card',
                    [
                        'ITEM' => $item,
                        'VUE_CONTROLS' => true
                    ]
                );
            }
            ?>
        </div>
        <?
        if ($arResult['pagination']['pageCount'] > 1) {
            service(FileService::class)->includeFile('pagination', [
                'pagination' => PaginationDTO::make($arResult['pagination'])
            ]);
        }
        ?>
    </div>
</div>