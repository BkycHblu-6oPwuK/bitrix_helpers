<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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

$date = new \Bitrix\Main\Type\DateTime($arResult["DATE_CREATE"]);
$printDate = $date -> format('d.m.Y');
$attrDate = $date -> format('Y-m-d');

$prop = $arResult["PROPERTIES"];
?>

<div class="article-detail">

    <? if($prop["PROP_BLOCK1_ACT"]["VALUE"] === "Y"): ?>
    <div class="article-block" data-sort="<?=$prop["PROP_BLOCK1_SORT"]["VALUE"] ?>">
        <div class="article-block__text">
            <div class="article-block__title">
                <time datetime="<?=$attrDate ?>"><?=$printDate ?></time>
                <p class="main-text"><?=$prop["PROP_BLOCK1_MAIN_TEXT"]["VALUE"] ?></p>
            </div>
            <div class="art-blocks">
                <? if(!empty($prop["PROP_BLOCK1_TEXT"]["~VALUE"])): ?>
                <?=$prop["PROP_BLOCK1_TEXT"]["~VALUE"]["TEXT"] ?>
                <? endif; ?>
            </div>
        </div>
        <? if($prop["PROP_BLOCK1_PHOTO"]["VALUE"]): ?>
        <div class="article-block__image">
            <img src="<?=CFile::GetPath($prop["PROP_BLOCK1_PHOTO"]["VALUE"]) ?>" alt="">
        </div>
        <? endif; ?>
    </div>
    <? endif; ?>

    <? if($prop["PROP_BLOCK2_ACT"]["VALUE"] === "Y"): ?>
        <div class="article-block column" data-sort="<?=$prop["PROP_BLOCK2_SORT"]["VALUE"] ?>">
            <div class="article-block__title">
                <h2><?=$prop["PROP_BLOCK2_TITLE"]["VALUE"] ?></h2>
                <p class="main-text"><?=$prop["PROP_BLOCK2_MAIN_TEXT"]["VALUE"] ?></p>
            </div>
            <div class="article-block__text">
                <div class="art-blocks">
                    <? if(!empty($prop["PROP_BLOCK2_TEXT1"]["~VALUE"])): ?>
                    <?=$prop["PROP_BLOCK2_TEXT1"]["~VALUE"]["TEXT"] ?>
                    <? endif; ?>
                </div>
            </div>
            <? if($prop["PROP_BLOCK2_PHOTO1"]["VALUE"]): ?>
                <div class="article-block__full-image">
                    <img src="<?=CFile::GetPath($prop["PROP_BLOCK2_PHOTO1"]["VALUE"]) ?>" alt="">
                </div>
            <? endif; ?>
            <div class="article-block__text">
                <div class="art-blocks">
                    <? if(!empty($prop["PROP_BLOCK2_TEXT2"]["~VALUE"])): ?>
                    <?=$prop["PROP_BLOCK2_TEXT2"]["~VALUE"]["TEXT"] ?>
                    <? endif; ?>
                </div>
            </div>
            <? if($prop["PROP_BLOCK2_PHOTO1"]["VALUE"]): ?>
                <div class="article-block__full-image">
                    <img src="<?=CFile::GetPath($prop["PROP_BLOCK2_PHOTO2"]["VALUE"]) ?>" alt="">
                </div>
            <? endif; ?>
            <div class="article-block__text">
                <div class="art-blocks">
                    <? if(!empty($prop["PROP_BLOCK2_TEXT3"]["~VALUE"])): ?>
                    <?=$prop["PROP_BLOCK2_TEXT3"]["~VALUE"]["TEXT"] ?>
                    <? endif; ?>
                </div>
            </div>
        </div>
    <? endif; ?>

    <? if($prop["PROP_BLOCK3_ACT"]["VALUE"] === "Y"): ?>
        <div class="article-block column" data-sort="<?=$prop["PROP_BLOCK3_SORT"]["VALUE"] ?>">
            <div class="article-block__title">
                <h2><?=$prop["PROP_BLOCK3_TITLE"]["VALUE"] ?></h2>
                <p class="main-text"><?=$prop["PROP_BLOCK3_MAIN_TEXT"]["VALUE"] ?></p>
            </div>
            <div class="article-block__text">
                <div class="art-blocks">
                    <? if(!empty($prop["PROP_BLOCK3_TEXT1"]["~VALUE"])): ?>
                    <?=$prop["PROP_BLOCK3_TEXT1"]["~VALUE"]["TEXT"] ?>
                    <? endif; ?>

                    <? if($prop["PROP_BLOCK3_PHOTO1"]["VALUE"]): ?>
                        <div class="article-block__half-image">
                            <img src="<?=CFile::GetPath($prop["PROP_BLOCK3_PHOTO1"]["VALUE"]) ?>" alt="">
                        </div>
                    <? endif; ?>
                </div>
            </div>

            <div class="article-block__text">
                <div class="art-blocks">
                    <? if($prop["PROP_BLOCK3_PHOTO2"]["VALUE"]): ?>
                        <div class="article-block__half-image">
                            <img src="<?=CFile::GetPath($prop["PROP_BLOCK3_PHOTO2"]["VALUE"]) ?>" alt="">
                        </div>
                    <? endif; ?>

                    <? if(!empty($prop["PROP_BLOCK3_TEXT2"]["~VALUE"])): ?>
                    <?=$prop["PROP_BLOCK3_TEXT2"]["~VALUE"]["TEXT"] ?>
                    <? endif; ?>
                </div>
            </div>

            <div class="article-block__text">
                <div class="art-blocks">
                    <? if(!empty($prop["PROP_BLOCK3_TEXT3"]["~VALUE"])): ?>
                        <?=$prop["PROP_BLOCK3_TEXT3"]["~VALUE"]["TEXT"] ?>
                    <? endif; ?>

                    <? if($prop["PROP_BLOCK3_PHOTO3"]["VALUE"]): ?>
                        <div class="article-block__half-image">
                            <img src="<?=CFile::GetPath($prop["PROP_BLOCK3_PHOTO3"]["VALUE"]) ?>" alt="">
                        </div>
                    <? endif; ?>
                </div>
            </div>

            <div class="article-block__text">
                <div class="art-blocks">
                    <? if($prop["PROP_BLOCK3_PHOTO4"]["VALUE"]): ?>
                        <div class="article-block__half-image">
                            <img src="<?=CFile::GetPath($prop["PROP_BLOCK3_PHOTO4"]["VALUE"]) ?>" alt="">
                        </div>
                    <? endif; ?>

                    <? if(!empty($prop["PROP_BLOCK3_TEXT4"]["~VALUE"])): ?>
                        <?=$prop["PROP_BLOCK3_TEXT4"]["~VALUE"]["TEXT"] ?>
                    <? endif; ?>
                </div>
            </div>

            <div class="article-block__text">
                <div class="art-blocks">
                    <? if(!empty($prop["PROP_BLOCK3_TEXT5"]["~VALUE"])): ?>
                        <?=$prop["PROP_BLOCK3_TEXT5"]["~VALUE"]["TEXT"] ?>
                    <? endif; ?>

                    <? if($prop["PROP_BLOCK3_PHOTO5"]["VALUE"]): ?>
                        <div class="article-block__half-image">
                            <img src="<?=CFile::GetPath($prop["PROP_BLOCK3_PHOTO5"]["VALUE"]) ?>" alt="">
                        </div>
                    <? endif; ?>
                </div>
            </div>
        </div>
    <? endif; ?>

    <? if($prop["PROP_BLOCK4_ACT"]["VALUE"] === "Y"): ?>
        <div class="article-block column" data-sort="<?=$prop["PROP_BLOCK4_SORT"]["VALUE"] ?>">
            <div class="article-block__title">
                <h2><?=$prop["PROP_BLOCK4_TITLE"]["VALUE"] ?></h2>
                <p class="main-text"><?=$prop["PROP_BLOCK4_MAIN_TEXT"]["VALUE"] ?></p>
            </div>

            <div class="article-block__text">
                <div class="art-blocks">
                    <? if(!empty($prop["PROP_BLOCK4_TEXT1"]["~VALUE"])): ?>
                    <?=$prop["PROP_BLOCK4_TEXT1"]["~VALUE"]["TEXT"] ?>
                    <? endif; ?>
                </div>
            </div>

        </div>
    <? endif; ?>

</div>