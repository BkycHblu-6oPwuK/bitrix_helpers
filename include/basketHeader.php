<?php

use Itb\Main\PageHelper;
?>
<div id="vue-header-cart">
    <a class="header__main-cart">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28"
            fill="none">
            <path d="M20.7267 11.5339L14 3.78174L7.27328 11.5339M20.7267 11.5339H25.2212L21.763 24.2188H6.1903L2.77881 11.5339H7.27328M20.7267 11.5339H7.27328"
                stroke="#0F1523" stroke-width="2" stroke-linejoin="round" />
        </svg>
    </a>
</div>

<script>
    window.addEventListener('DOMContentLoaded', () => window.vueApps.createCartHeader('<?= PageHelper::getBasketUrl() ?>').mount('#vue-header-cart'))
</script>
<?
