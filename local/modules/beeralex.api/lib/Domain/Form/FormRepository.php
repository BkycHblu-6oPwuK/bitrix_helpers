<?
declare(strict_types=1);
namespace Beeralex\Api\Domain\Form;

use Beeralex\Core\Model\FormTable;
use Beeralex\Core\Repository\Repository;

class FormRepository extends Repository
{
    public function __construct(bool $useDecompose = false)
    {
        return parent::__construct(FormTable::class, $useDecompose);
    }

    public function getIdByName(string $name): ?int
    {
        $form = $this->one(['=NAME' => $name], ['ID'], 86400);
        return $form['ID'] ? (int)$form['ID'] : null;
    }
}