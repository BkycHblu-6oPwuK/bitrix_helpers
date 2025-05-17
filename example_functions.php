<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

\Bitrix\Main\Diag\Debug::dumpToFile(array('ID' => $id, 'fields'=>$fields ),"","111111_logs/1.txt"); 

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
