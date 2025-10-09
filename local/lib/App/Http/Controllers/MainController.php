<?

namespace App\Http\Controllers;

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
}
