<?

namespace Beeralex\Catalog;

use Beeralex\Catalog\Cashbox\PrepaymentCheck;
use Beeralex\Catalog\ExtraService\MyPriceExtraService;
use Beeralex\Catalog\Restriction\UserRestriction;
use Beeralex\Core\Service\PathService;
use Bitrix\Main\EventResult;

class EventHandlers
{
    public static function onSalePaySystemRestrictionsClassNamesBuildList()
    {
        $pathService = \service(PathService::class);
        $filepath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $pathService->classFile(UserRestriction::class));
        return new EventResult(
            EventResult::SUCCESS,
            [
                UserRestriction::class => $filepath,
            ]
        );
    }

    public static function onSaleCashboxRestrictionsClassNamesBuildList()
    {
        $pathService = \service(PathService::class);
        $filepath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $pathService->classFile(UserRestriction::class));
        return new EventResult(
            EventResult::SUCCESS,
            [
                UserRestriction::class => $filepath,
            ]
        );
    }

    public static function onGetCustomCheckList()
    {
        $pathService = \service(PathService::class);
        $filepath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $pathService->classFile(PrepaymentCheck::class));
        return new EventResult(
            EventResult::SUCCESS,
            [
                PrepaymentCheck::class => $filepath,
            ]
        );
    }

    public static function onSaleDeliveryExtraServicesClassNamesBuildList()
    {
        $pathService = \service(PathService::class);
        $filepath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $pathService->classFile(MyPriceExtraService::class));
        return new EventResult(
            EventResult::SUCCESS,
            [
                MyPriceExtraService::class => $filepath
            ]
        );
    }
}
