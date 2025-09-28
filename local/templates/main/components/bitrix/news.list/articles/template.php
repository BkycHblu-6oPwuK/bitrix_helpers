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

<section class="section-slider">
    <div class="articles__title-block">
        <h2>Полезные статьи</h2>
        <? if ($arParams['LINK_TO_ALL']): ?>
            <a href="<?= $arParams['LINK_TO_ALL'] ?>">Показать все</a>
        <? endif; ?>
    </div>
    <div class="articles">
        <div class="articles__container">
            <div class="swiper-wrapper">
                <? foreach ($arResult["ITEMS"] as $arItem): ?>
                    <?
                    $file = CFile::ResizeImageGet($arItem["PREVIEW_PICTURE"], ['width' => 576, 'height' => 300], BX_RESIZE_IMAGE_PROPORTIONAL, true);
                    $img = '<img src="' . $file['src'] . '" width="' . $file['width'] . '" height="' . $file['height'] . '" />';
                    ?>
                    <div class="articles__item swiper-slide">
                        <div class="articles__item-image">
                            <?= $img ?>
                        </div>
                        <div class="articles__item-title">
                            <a href="<?= $arItem["DETAIL_PAGE_URL"] ?>">
                                <p class="articles__item__text"><?= $arItem["NAME"] ?></p>
                            </a>
                        </div>
                    </div>
                <? endforeach; ?>

            </div>
        </div>
    </div>
</section>