<?
declare(strict_types=1);
namespace Beeralex\Api\Domain\Form;

use Beeralex\Core\Model\FormAnswerTable;
use Beeralex\Core\Repository\Repository;

class FormAnswerRepository extends Repository
{
    public function __construct(bool $useDecompose = false)
    {
        return parent::__construct(FormAnswerTable::class, $useDecompose);
    }

    public function getMapByTitles(int $formId, array $titles): ?array
    {
        if(empty($titles)) {
            return null;
        }
        $fields = $this->all(['=FIELD.FORM_ID' => $formId, '=FIELD.TITLE' => $titles], ['ID', 'FIELD_TITLE' => 'FIELD.TITLE', 'FIELD_SID' => 'FIELD.SID', 'FIELD_TYPE'], [], 86400);
        $result = [];
        foreach ($fields as $field) {
            $result[$field['FIELD_TITLE']] = [
                'ID' => $field['ID'],
                'TYPE' => $field['FIELD_TYPE'],
                'SID' => $field['FIELD_SID'],
            ];
        }
        return $result;
    }
}