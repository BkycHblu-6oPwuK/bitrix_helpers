<?php

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use App\Notification\Enum\Channels;
use App\Notification\Enum\Types;
use App\Notification\Services\NotificationPreferenceService;
use App\User\User;

class BeeralexNotificationController extends Controller
{
    protected readonly NotificationPreferenceService $pereferenceService;

    public function configureActions()
    {
        return [
            'addPreference' => [
                'prefilters' => [
                    new Csrf(),
                    new HttpMethod([HttpMethod::METHOD_POST]),
                    new Authentication()
                ],
            ],
        ];
    }

    public function __construct(?\Bitrix\Main\Request $request = null)
    {
        parent::__construct($request);
        $this->pereferenceService = new NotificationPreferenceService();
    }

    public function addPreferenceAction($type, $channel, $enabled)
    {
        try {
            $userId = User::current()->getId();
            $type = Types::get($type);
            $channel = Channels::get($channel);
            $enabled = (bool)$enabled;
            $this->pereferenceService->createOrUpdate($userId, $type, $channel, $enabled);
            return [
                'success' => true,
                'value' => $enabled
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
            ];
        }
    }
}
