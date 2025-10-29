<?

use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\Controller;
use Beeralex\Core\Helpers\LocationHelper;

class BeeralexLocationController extends Controller
{
    public function configureActions()
    {
        return [
            'get' => [
                'prefilters' => [
                    new Csrf(),
                ],
                'postfilters' => [],
            ],
        ];
    }

    public function getAction($query, $pageSize, $page)
    {
        try {
            return [
                'success' => true,
                'data' => LocationHelper::find($query, $pageSize, $page)
            ];
        } catch (\Exception $e) {
        }
        return [
            'success' => false
        ];
    }
}