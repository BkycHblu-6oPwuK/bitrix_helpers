<?php

use Beeralex\Core\Config\Config;
use App\Main\PageHelper;
?>
<script id="js-data" type="application/json">
	<?= \Bitrix\Main\Web\Json::encode([
		'sessid' => bitrix_sessid(),
		'actionLoadItems' => Config::getInstance()->actionLoadItems,
		'pages' => [
			'catalogPageUrl' => PageHelper::getCatalogPageUrl(),
		],
	]) ?>
</script>

<script async src="https://telegram.org/js/telegram-widget.js?7"
        data-telegram-login="my_local_site_auth_bot"
        data-size="large"
        data-userpic="false"
        data-request-access="write"
        data-auth-url="https://127.0.0.1/user/auth/telegram/">
</script>