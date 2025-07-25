<?

use Itb\Core\Assets\Vite;

Vite::getInstance()->includeAssets(['src/app/cart/index.js']);
?>
<div class="section__container">
    <div id="vue-basket" style="min-height: 400px"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.vueApps.createCart(<?=$arParams['PATH_TO_ORDER']?>).mount('#vue-basket');
    })
</script>