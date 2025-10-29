<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Context;
use Beeralex\Core\Helpers\WebHelper;
use Bitrix\Main\Web\Json;

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>

<div class="articles-page" id="vue-articles">
    <div class="articles-list">

        <?foreach($arResult["ITEMS"] as $arItem):?>
            <?
            $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

            $date = new \Bitrix\Main\Type\DateTime($arItem["DATE_CREATE"]);
            $printDate = $date -> format('d.m.Y');
            $attrDate = $date -> format('Y-m-d');

            $wideCard = $arItem["PROPERTIES"]["PROP_WIDE_CARD"]["VALUE"] === "Y" ? "big" : "";
            $image = $arItem["PROPERTIES"]["PROP_WIDE_CARD"]["VALUE"] === "Y"
                ? $arItem["PROPERTIES"]["PROP_IMAGE_LIST_WIDE"]["VALUE"]
                : $arItem["PROPERTIES"]["PROP_IMAGE_LIST"]["VALUE"];
            $imageSrc = CFile::GetPath($image)
            ?>
            <a href="<?=$arItem["DETAIL_PAGE_URL"] ?>" class="articles-list__item <?=$wideCard ?>" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                <div class="articles-list__item-image">
                    <img src="<?=$imageSrc ?>" alt="">
                </div>
                <div class="articles-list__item-info">
                    <time datetime="<?=$attrDate ?>"><?=$printDate ?></time>
                    <span class="articles-list__item-title"><?=$arItem["NAME"] ?></span>
                    <p><?=$arItem["PREVIEW_TEXT"] ?></p>
                    <span class="articles-list__item-link">Подробнее</span>
                </div>
            </a>
        <?endforeach;?>

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

<script>
    document.addEventListener("DOMContentLoaded", () => {
        window.vueApps.createArticles(<?= Json::encode(['articlesList' => $arResult])?>)
            .mount('#vue-articles')
    })
</script>




