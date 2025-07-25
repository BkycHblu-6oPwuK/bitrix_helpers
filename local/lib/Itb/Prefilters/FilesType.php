<?php

namespace Itb\Prefilters;

use Bitrix\Main\Application;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Engine\ActionFilter\Base;

final class FilesType extends Base
{
    private $enabled;
    private $allowedExtensions;

    public function __construct(bool $enabled = true, array $allowedExtensions = [])
    {
        $this->enabled = $enabled;
        $this->allowedExtensions = array_map('strtolower', $allowedExtensions);
        parent::__construct();
    }

    public function listAllowedScopes()
    {
        return [
            Controller::SCOPE_AJAX,
        ];
    }

    public function onBeforeAction(Event $event)
    {
        if (!$this->enabled || empty($this->allowedExtensions)) {
            return null;
        }

        $request = Application::getInstance()->getContext()->getRequest();
        $files = $request->getFileList()->getValues();
        
        if (!empty($files)) {
            foreach ($files as $arFiles) {
                foreach ($arFiles['name'] as $fileName) {
                    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    if (!in_array($extension, $this->allowedExtensions, true)) {
                        $this->addError(new Error(
                            "Недопустимое расширение файла: $extension.",
                            415
                        ));
                        return new EventResult(EventResult::ERROR, null, null, $this);
                    }
                }
            }
        }
        
        return null;
    }
}
