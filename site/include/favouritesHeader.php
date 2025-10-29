<?php
use App\Main\PageHelper;
?>
<div id="vue-favourites-header">
    <span class="header__main-favourites">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28"
            fill="none">
            <path fill-rule="evenodd" clip-rule="evenodd"
                d="M14 6.99994C11.9007 4.55342 8.39274 3.79733 5.76243 6.03761C3.13213 8.27789 2.76182 12.0235 4.82741 14.6731C6.54481 16.8761 11.7423 21.5223 13.4457 23.0261C13.6363 23.1943 13.7316 23.2784 13.8427 23.3115C13.9397 23.3403 14.0459 23.3403 14.1429 23.3115C14.254 23.2784 14.3493 23.1943 14.5399 23.0261C16.2434 21.5223 21.4408 16.8761 23.1582 14.6731C25.2238 12.0235 24.8987 8.25433 22.2232 6.03761C19.5477 3.82089 16.0993 4.55342 14 6.99994Z"
                stroke="#0F1523" stroke-width="2" stroke-linecap="round" />
        </svg>
    </span>
</div>
<script>
    window.addEventListener('DOMContentLoaded', () => window.vueApps.createFavouriteSmallHeader(<?=PageHelper::getFavouritesUrl()?>).mount('#vue-favourites-header'))
</script>
<?
