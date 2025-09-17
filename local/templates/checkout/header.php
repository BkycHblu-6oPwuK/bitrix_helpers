<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Page\Asset;
use Itb\Core\Assets\Vite;
use Itb\Main\PageHelper;
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

<body>
    <div id="panel"><? $APPLICATION->ShowPanel(); ?></div>


    <div class="header-min">
        <div class="header-min__container">
            <a href="<?= PageHelper::getCatalogPageUrl() ?>" class="header-min__return">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="19" viewBox="0 0 18 19" fill="none">
                    <path d="M11.25 14L6.75 9.5L11.25 5" stroke="#111827" stroke-width="2" stroke-linecap="square"></path>
                </svg>
                <span>Вернуться в каталог</span>
            </a>
            <a href="/" class="header-min__logo">DZHAVADOFF</a>
            <a href="tel:83812927729" class="header-min__tel">8 3812 927-729</a>
            <div id="vue-header-mobile-search" class="header-min__search">
                <div class="header__main-search"> <!-- поиск -->
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
            <div class="header-min__phone">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M12.5417 4.16671C13.3557 4.32551 14.1037 4.72359 14.6901 5.30999C15.2765 5.89639 15.6746 6.64443 15.8334 7.45837M12.5417 0.833374C14.2328 1.02124 15.8097 1.77852 17.0136 2.98088C18.2175 4.18324 18.9767 5.75922 19.1667 7.45004M18.3334 14.1V16.6C18.3343 16.8321 18.2868 17.0618 18.1938 17.2745C18.1008 17.4871 17.9645 17.678 17.7934 17.8349C17.6224 17.9918 17.4205 18.1113 17.2007 18.1856C16.9808 18.26 16.7479 18.2876 16.5167 18.2667C13.9524 17.9881 11.4892 17.1118 9.32505 15.7084C7.31157 14.4289 5.60449 12.7219 4.32505 10.7084C2.91669 8.53438 2.04025 6.0592 1.76671 3.48337C1.74589 3.25293 1.77328 3.02067 1.84713 2.80139C1.92098 2.58211 2.03969 2.38061 2.19568 2.20972C2.35168 2.03883 2.54155 1.9023 2.75321 1.80881C2.96486 1.71532 3.19366 1.66693 3.42505 1.66671H5.92505C6.32947 1.66273 6.72154 1.80594 7.02818 2.06965C7.33482 2.33336 7.53511 2.69958 7.59171 3.10004C7.69723 3.9001 7.89292 4.68565 8.17505 5.44171C8.28717 5.73998 8.31143 6.06414 8.24497 6.37577C8.17851 6.68741 8.0241 6.97347 7.80005 7.20004L6.74171 8.25837C7.92801 10.3447 9.65542 12.0721 11.7417 13.2584L12.8 12.2C13.0266 11.976 13.3127 11.8216 13.6243 11.7551C13.9359 11.6887 14.2601 11.7129 14.5584 11.825C15.3144 12.1072 16.1 12.3029 16.9 12.4084C17.3049 12.4655 17.6745 12.6694 17.9388 12.9813C18.2031 13.2932 18.3435 13.6914 18.3334 14.1Z" stroke="black" stroke-width="1.5" stroke-linecap="square"></path>
                </svg>
            </div>
        </div>
        <!-- для vue телепорта -->
        <div id="modal-container-header"></div>
    </div>

    <div class="section">