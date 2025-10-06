<?

use Beeralex\Core\Assets\Vite;

Vite::getInstance()->includeAssets(['src/app/dressing/index.js']);
?>
<div class="section__container">
    <div id="vue-dressing" style="min-height: 400px"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.vueApps.createDressing().mount('#vue-dressing');
    })
</script>