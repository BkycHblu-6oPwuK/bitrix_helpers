<?

use Beeralex\Core\Assets\Vite;

Vite::getInstance()->includeAssets([
    'src/app/profile/index.js'
]);

?>
<div class="section__container">
    <div id="vue-profile"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => window.vueApps.createProfile().mount('#vue-profile'));
</script>