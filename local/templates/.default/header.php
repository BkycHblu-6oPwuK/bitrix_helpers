<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

global $APPLICATION;
use Bitrix\Main\Page\Asset;
use Itb\Core\Assets\Vite;
use Itb\Enum\Gender;
use Itb\Helpers\CatalogHelper;
use Itb\Helpers\PageHelper;
use Itb\User\User;

$curPage = $APPLICATION->GetCurPage();
$isAuthorized = User::current()->isAuthorized();
?>
<!DOCTYPE html>

<html xml:lang="<?= LANGUAGE_ID ?>" lang="<?= LANGUAGE_ID ?>">

<head>
    <title><? $APPLICATION->ShowTitle() ?></title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, width=device-width">
    <link rel="shortcut icon" type="image/x-icon" href="<?= SITE_DIR ?>favicon.ico" />
    <? $APPLICATION->ShowHead(); ?>

    <?
    Asset::getInstance()->addString("<script src='https://api-maps.yandex.ru/2.1/?apikey=47dfa583-67de-4539-b95e-8c85e82fd8b7&lang=ru_RU&suggest_apikey=6711073e-a2ea-498f-9f18-1d9de8d21df4' type='text/javascript' defer></script>");
    Vite::getInstance()->includeAssets(['src/common/js/bundle.js']);
    ?>
</head>

<body class="header-shadow body-footer">
    <div id="panel"><? $APPLICATION->ShowPanel(); ?></div>

    <header class="header">
        <div class="header__top">
            <div class="header__top-container">
                <div class="header__top-contacts">
                    <span>Омск, пр-т Комарова, 2/2, бутик 265</span>
                    <a href="tel:88006758899">8 (3812) 72-72-96</a>
                </div>
                <div class="header__top-links">
                    <a href="">Условия рассрочки</a>
                    <a href="/buyers/how-to-buy/">Раздел покупателям</a>
                </div>
            </div>
        </div>
    </header>
    <div class="m-header__top">
        <div class="m-header__top-menu-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M2.4375 4.87549H17.0634" stroke="#111827" stroke-width="1.5" stroke-linecap="square" />
                <path d="M2.4375 9.75049H17.0634" stroke="#111827" stroke-width="1.5" stroke-linecap="square" />
                <path d="M2.4375 14.626H9.75044" stroke="#111827" stroke-width="1.5" stroke-linecap="square" />
            </svg>
        </div>
        <div class="m-header__top-logo">
            <a href="/">Dzhavadoff</a>
        </div>
        <div id="vue-header-mobile-search">
            <div class="m-header__top-search">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" fill="none">
                    <path d="M12.8333 22.1667C17.988 22.1667 22.1667 17.988 22.1667 12.8333C22.1667 7.67868 17.988 3.5 12.8333 3.5C7.67868 3.5 3.5 7.67868 3.5 12.8333C3.5 17.988 7.67868 22.1667 12.8333 22.1667Z"
                        stroke="#0F1523" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M24.5001 24.4998L19.425 19.4248" stroke="#0F1523" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </div>
        </div>
    </div>
    <div class="m-header__menu">
        <div class="m-header__menu-overlay header-modal">
            <div class="m-header__menu-container">
                <div>
                    <div class="m-header__menu-logo">
                        <span>Dzhavadoff</span>
                        <div class="m-header__menu-logo-close-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                                fill="none">
                                <path d="M15 5L5 15" stroke="#0E1220" stroke-width="1.39294" stroke-linecap="square" />
                                <path d="M5 5L15 15" stroke="#0E1220" stroke-width="1.39294" stroke-linecap="square" />
                            </svg>
                        </div>
                    </div>
                    <div id="m-header-profile"></div>
                    <div class="m-header__menu-tabs">
                        <a href="<?=PageHelper::getProfileOrdersPageUrl()?>">Заказы</a>
                        <a href="<?=PageHelper::getProfilePageUrl()?>">Мои данные</a>
                        <a href="<?=PageHelper::getProfileQuestionsPageUrl()?>">Вопросы / ответы</a>
                    </div>
                </div>
                <div class="m-header__menu-contacts">
                    <a class="m-header__menu-contacts-tel" href="tel:88006758899">8 800 675 88 99</a>
                    <span class="m-header__menu-contacts-address">Омск, пр-т Комарова, 2/2, бутик 265</span>
                    <a class="m-header__menu-contacts-credit" href="#">Условия рассрочки</a>
                    <a class="m-header__menu-contacts-about" href="/buyers/how-to-buy/">Раздел покупателям</a>
                </div>
            </div>
        </div>
    </div>
    <div class="header__section <?= $curPage != '/' ? 'header__section_border' : '' ?>">
        <div class="header__relative">
            <div class="header__container">
                <div class="header__main">
                    <div class="header__main-tabs">
                        <?
                        $APPLICATION->IncludeComponent(
                            'itb:menu',
                            '.default',
                            [
                                'iblockId' => CatalogHelper::getCatalogIblockId(),
                                'gender' => Gender::WOMAN,
                                'CACHE_TYPE' => 'A',
                                'CACHE_TIME' => 86400,
                            ],
                            null,
                            ['HIDE_ICONS' => 'Y']
                        );
                        $APPLICATION->IncludeComponent(
                            'itb:menu',
                            '.default',
                            [
                                'iblockId' => CatalogHelper::getCatalogIblockId(),
                                'gender' => Gender::MAN,
                                'CACHE_TYPE' => 'A',
                                'CACHE_TIME' => 86400,
                            ],
                            null,
                            ['HIDE_ICONS' => 'Y']
                        );
                        ?>
                    </div>
                    <div class="header__main-logo">
                        <a href="/">Dzhavadoff</a>
                    </div>

                    <div class="header__main-icons">

                        <div id="vue-header-search">
                            <div class="header__main-search">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28"
                                    fill="none">
                                    <path d="M12.8333 22.1667C17.988 22.1667 22.1667 17.988 22.1667 12.8333C22.1667 7.67868 17.988 3.5 12.8333 3.5C7.67868 3.5 3.5 7.67868 3.5 12.8333C3.5 17.988 7.67868 22.1667 12.8333 22.1667Z"
                                        stroke="#0F1523" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M24.5001 24.4998L19.425 19.4248" stroke="#0F1523" stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                        </div>

                        <?

                        $APPLICATION->IncludeComponent(
                            "itb:auth",
                            "vue_template",
                            [],
                            false
                        );

                        $APPLICATION->IncludeFile(
                            SITE_DIR . "include/favouritesHeader.php",
                            [],
                            array("MODE" => "PHP")
                        );

                        $APPLICATION->IncludeFile(
                            SITE_DIR . "include/dressingHeader.php",
                            [],
                            array("MODE" => "PHP")
                        );

                        $APPLICATION->IncludeFile(
                            SITE_DIR . "include/basketHeader.php",
                            [],
                            array("MODE" => "PHP")
                        );

                        ?>

                    </div>
                </div>
            </div>
        </div>

        <!-- для vue телепорта -->
        <div id="modal-container-header"></div>
    </div>

    <? if ($curPage != "/") : ?>
        <div class="breadcrumb-container">
            <div class="section__container">
                <div class="col" id="navigation">
                    <? $APPLICATION->IncludeComponent(
                        "bitrix:breadcrumb",
                        "universal",
                        array(
                            "START_FROM" => "0",
                            "PATH" => "",
                            "SITE_ID" => "-"
                        ),
                        false,
                        array('HIDE_ICONS' => 'Y')
                    ); ?>
                </div>
                <? if (!str_starts_with($curPage, PageHelper::getProductPageUrl())): ?>
                    <h1 class="page-title"><? $APPLICATION->ShowTitle(false); ?></h1>
                <? endif; ?>
            </div>
        </div>
    <? endif ?>

    <div class="section">