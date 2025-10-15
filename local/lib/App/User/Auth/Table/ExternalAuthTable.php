<?
namespace App\User\Auth\Table;

use Beeralex\Core\Traits\TableManagerTrait;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;

class ExternalAuthTable extends DataManager
{
    use TableManagerTrait;
    
    public static function getTableName(): string
    {
        return 'app_external_auth';
    }

    public static function getMap(): array
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new IntegerField('USER_ID', [
                'required' => true,
            ]),
            new StringField('SERVICE', [
                'required' => true,
                'size' => 50,
            ]),
            new StringField('EXTERNAL_ID', [
                'required' => true,
                'size' => 255,
            ]),
            new StringField('ACCESS_TOKEN', [
                'size' => 512,
            ]),
            new StringField('REFRESH_TOKEN', [
                'size' => 512,
            ]),
            new DatetimeField('CREATED_AT', [
                'default_value' => new \Bitrix\Main\Type\DateTime(),
            ]),
            new DatetimeField('UPDATED_AT', [
                'default_value' => new \Bitrix\Main\Type\DateTime(),
            ]),
        ];
    }
}
