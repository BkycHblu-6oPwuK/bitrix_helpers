<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
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
$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
?>
<div class="swiper banner__container">
    <div class="swiper-wrapper">
        <? foreach ($arResult["ITEMS"] as $arItem): ?>
            <div class="swiper-slide banner" style="background:url('<?= $arItem["PREVIEW_PICTURE"]["SRC"] ?>') center/cover no-repeat">
                <div class="d-flex flex__col align__center absolute abs__center">
                    <span class="banner__title"><?= $arItem["PROPERTIES"]["PROP_TITLE"]["~VALUE"] ?></span>
                    <a href="<?= $arItem["PROPERTIES"]["PROP_LINK"]["VALUE"] ?>" class="btn banner__link">
                        Подробнее
                    </a>
                </div>
            </div>
        <? endforeach; ?>
    </div>

    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>

    <div class="swiper-pagination"></div>
</div>