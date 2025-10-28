<?
namespace Beeralex\User;

use Beeralex\User\Auth\Social\Services\BitrixTelegramService;

class EventHandlers 
{
    public static function onAuthServicesBuildList()
    {
        $result = [];
        $services = [
            BitrixTelegramService::class
        ];
        foreach($services as $serviceClass) {
            $service = new $serviceClass();
            $result = [
                [
                    'ID' => $service->getId(),
                    'CLASS' => $serviceClass,
                    'NAME' => $service->getName(),
                    'ICON' => $service->getIcon(),
                ]
            ];
        }
        return $result;
    }
}