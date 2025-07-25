<?php

use Bitrix\Main\Web\Json;
use Itb\Core\Assets\Vite;

//Vite::getInstance()->includeAssets(['src/app/auth/index.js']); импорт в bundle
?>

<div id="vue-auth" class="header__main-profile">
    <a class="header__main-profile">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28"
            fill="none">
            <path d="M5.8335 24.5C5.8335 19.9897 9.48984 16.3333 14.0002 16.3333C18.5105 16.3333 22.1668 19.9897 22.1668 24.5M18.6668 8.16667C18.6668 10.744 16.5775 12.8333 14.0002 12.8333C11.4228 12.8333 9.3335 10.744 9.3335 8.16667C9.3335 5.58934 11.4228 3.5 14.0002 3.5C16.5775 3.5 18.6668 5.58934 18.6668 8.16667Z"
                stroke="#0F1523" stroke-width="2" stroke-linecap="round" />
        </svg>
    </a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => window.vueApps.createAuth(<?=Json::encode($arResult)?>).mount('#vue-auth'))
</script>