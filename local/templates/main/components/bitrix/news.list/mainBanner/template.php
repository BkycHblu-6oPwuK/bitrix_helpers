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
<div class="banner__container">
    <? foreach ($arResult["ITEMS"] as $arItem): ?>
        <div class="banner" style="background:url('<?= $arItem["PREVIEW_PICTURE"]["SRC"] ?>') center/cover no-repeat">
            <span class="banner__title"><?= $arItem["PROPERTIES"]["PROP_TITLE"]["~VALUE"] ?></span>
            <a href="<?= $arItem["PROPERTIES"]["PROP_LINK"]["VALUE"] ?>" class="banner__link">
                Подробнее
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
                    <path d="M7.00011 18.5607L5.93945 17.5L15.1895 8.25H6.25011V6.75H17.7501V18.25H16.2501V9.31066L7.00011 18.5607Z" fill="white" />
                </svg>
            </a>
        </div>
    <? endforeach; ?>
</div>