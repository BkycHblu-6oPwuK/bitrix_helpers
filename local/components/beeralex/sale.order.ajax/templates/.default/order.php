<?php

use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Main\Web\Json;
use Beeralex\Core\Assets\Vite;

Vite::getInstance()->includeAssets(['src/app/checkout/index.js']);

$arResult['JS_DATA']['signedParams'] = (new Signer())->sign(base64_encode(serialize($arParams)), 'sale.order.ajax');
$arResult['JS_DATA']['siteId'] = $component->getSiteId();
?>
<div class="section__container">
    <div id="checkout"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => window.vueApps.createCheckout(<?= Json::encode($arResult['JS_DATA']) ?>).mount('#checkout'));
</script>