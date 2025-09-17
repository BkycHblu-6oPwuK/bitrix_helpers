<?
use Itb\User\Enum;
if (empty($arResult['menu'])) return;
$isWoman = $arParams['gender'] === Gender::WOMAN;
$title = $isWoman ? 'Женщинам' : 'Мужчинам';
$classAlias = $isWoman ? 'women' : 'men';

if (!function_exists('renderMenu')) {
    function renderMenu(array $menuItems)
    {
        echo '<div class="header__main-catalog-col">';
        foreach ($menuItems as $menu) {
            $rootMenuClass = $menu["UF_MENU_ROOT"] ? 'header__main-catalog-col-title' : '';

            echo '<a href="' . htmlspecialchars($menu['SECTION_PAGE_URL']) . '" class="header__main-catalog-item lvl-' . $menu['DEPTH_LEVEL'] . ' ' . $rootMenuClass . '">';
            echo htmlspecialchars($menu['NAME']);
            echo '</a>';
        }
        echo '</div>';
    }
}
?>
<div class="header__main-tabs-<?= $classAlias ?> hover-menu-<?= $classAlias ?> header-main-menu">
    <span><?= $title ?></span>
    <teleport to="#modal-container-header">
        <div class="header__main-<?= $classAlias ?>-catalog-container header-modal">
            <div class="header__main-catalog hover-menu-<?= $classAlias ?>">
                <div class="header__main-catalog-list">
                    <div class="header__main-catalog-col-mobile">
                        <a href="<?= $arResult['catalogUrl'] ?>" class="header__main-catalog-item">Все позиции</a>
                    </div>

                    <?
                    foreach ($arResult['menu'] as $items) {
                        renderMenu($items);
                    }
                    ?>
                </div>
            </div>
        </div>
    </teleport>
</div>