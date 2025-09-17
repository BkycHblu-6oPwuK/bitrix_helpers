<?php

use Itb\Core\Config;
use Itb\Main\PageHelper;
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