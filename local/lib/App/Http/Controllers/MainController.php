<?

namespace App\Http\Controllers;

use Beeralex\Oauth2\ActionFilter\Oauth2Token;
use Bitrix\Main\Engine\Controller;

class MainController extends Controller
{
    public function configureActions()
    {
        return [
            'index' => [
                'prefilters' => [],
            ],
            'man' => [
                'prefilters' => [],
            ],
            'woman' => [
                'prefilters' => [],
            ],
            'test' => [
                'prefilters' => [
                    new Oauth2Token()
                ],
            ],
        ];
    }

    public function indexAction()
    {
        return $this->renderView('/local/views/index/index.php');
    }

    public function manAction()
    {
        return $this->renderView('/local/views/index/man.php');
    }

    public function womanAction()
    {
        return $this->renderView('/local/views/index/woman.php');
    }
    
    public function testAction()
    {
        return [11111];
    }
}
