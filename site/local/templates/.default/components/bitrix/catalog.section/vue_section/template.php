<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
?>
<div>
    <?
    if (!empty($arResult['ITEMS'])) :
    ?>
        <div class="catalog-type">
            <div class="catalog-type__container">
                <?
                foreach ($arResult['ITEMS'] as $item) {
                    $APPLICATION->IncludeComponent(
                        'bitrix:catalog.item',
                        'main-card',
                        [
                            'ITEM' => $item,
                        ]
                    );
                }
                ?>
            </div>
            <?
            if ($arResult['PAGINATION']['pageCount'] > 1) {
                $APPLICATION->IncludeComponent(
                    'beeralex:pagination',
                    '.default',
                    [
                        'PAGINATION' => $arResult['PAGINATION'],
                    ]
                );
            }
            ?>
        </div>
    <? endif; ?>
</div>