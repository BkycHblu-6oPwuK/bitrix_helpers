<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

global $APPLICATION;

use Bitrix\Main\Page\Asset;
use Itb\Core\Assets\Vite;
use App\User\User;
use Itb\Core\Helpers\IblockHelper;

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

    <header>
        <?php
        $APPLICATION->IncludeComponent(
            'itb:menu',
            '.default',
            [
                'iblockId' => IblockHelper::getIblockIdByCode('catalog'),
                //'type' => TypesCatalog::MAN,
                'CACHE_TYPE' => 'A',
                'CACHE_TIME' => 86400,
            ],
            null,
            ['HIDE_ICONS' => 'Y']
        );
        // $APPLICATION->IncludeComponent(
        //     'itb:menu',
        //     '.default',
        //     [
        //         'iblockId' => IblockHelper::getIblockIdByCode('catalog'),
        //         'type' => TypesCatalog::WOMAN,
        //         'CACHE_TYPE' => 'A',
        //         'CACHE_TIME' => 86400,
        //     ],
        //     null,
        //     ['HIDE_ICONS' => 'Y']
        // );
        ?>
    </header>

    <div class="section">