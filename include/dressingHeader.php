<?php

use App\Main\PageHelper;
?>
<div id="vue-dressing-header">
    <a class="header__main-dressing">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28"
            fill="none">
            <path d="M10.8106 6.77484C10.8106 5.12719 12.1893 3.7915 13.8899 3.7915C15.5906 3.7915 16.9693 5.12719 16.9693 6.77484C16.9693 7.94215 16.2773 8.95287 15.2691 9.44298C14.5633 9.78605 13.8899 10.3783 13.8899 11.1433V13.0088M13.8899 13.0088C13.6063 13.026 13.3261 13.1097 13.075 13.26L3.17112 19.8865C1.66307 20.7891 2.32334 23.0415 4.096 23.0415H23.9039C25.6765 23.0415 26.3368 20.7891 24.8287 19.8865L14.9248 13.26C14.609 13.0709 14.2468 12.9872 13.8899 13.0088Z"
                stroke="#0F1523" stroke-width="2" stroke-linecap="round" />
        </svg>
    </a>
</div>
<script>
    window.addEventListener('DOMContentLoaded', () => window.vueApps.createDressingHeader('<?= PageHelper::getDressingUrl() ?>').mount('#vue-dressing-header'))    
</script>
<?
