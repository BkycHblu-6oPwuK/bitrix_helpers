<?php

namespace App\Main\Prefilters;

use Bitrix\Main\Application;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Engine\ActionFilter\Base;

final class FilesSize extends Base
{
	private $enabled;
	private $sizeMb;

	public function __construct(bool $enabled = true, float $sizeMb)
	{
		$this->enabled = $enabled;
		$this->sizeMb = $sizeMb;
		parent::__construct();
	}

	/**
	 * List allowed values of scopes where the filter should work.
	 * @return array
	 */
	public function listAllowedScopes()
	{
		return [
			Controller::SCOPE_AJAX,
		];
	}

	public function onBeforeAction(Event $event)
	{
		if (!$this->enabled)
		{
			return null;
		}
		$request = Application::getInstance()->getContext()->getRequest();
		$files = $request->getFileList()->getValues();
		$resultSize = 0;
		$stringFile = 'файла';
		if(!empty($files)){
			foreach($files as $arFiles){
				if($arFiles['size'] && count($arFiles['size']) > 1){
					$stringFile = 'файлов';
				}
				foreach($arFiles['size'] as $size){
					$resultSize += $size;
				}
			}
			$resultSize = (int)(($resultSize / 1000000) * 100) / 100;
			if($resultSize > $this->sizeMb){
				$this->addError(new Error(
					"Размер $stringFile должен быть до {$this->sizeMb} мб.",
					413
				));
	
				return new EventResult(EventResult::ERROR, null, null, $this);
			}
		}
		return null;
	}
}