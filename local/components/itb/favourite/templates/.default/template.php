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
            $APPLICATION->IncludeComponent(
                'itb:pagination',
                '.default',
                [
                    'PAGINATION' => $arResult['pagination'],
                    'HIDE_SHOW_MODE' => true
                ]
            );
        }
        ?>
    </div>
</div>