# Валидация

Классы находятся в прострастве имен \Bitrix\Main\Validation. Там можете смотреть какие валидаторы есть из коробки.

В пространстве имен \Validation\Rule находятся атрибуты, с помощью которых можно валидировать свойства в ваших классах.

В пространстве имен \Validation\Validator находятся сами валидаторы. У них у всех есть метод validate и все они возвращают ValidationResult.

И есть \Validation\ValidationService - они валидирует свойства переданного объекта в метод validate и так же возвращает ValidationResult.

Пример использования:

```php
/**
 * валидатор проверяет длину строки
 */
$validator = new \Bitrix\Main\Validation\Validator\LengthValidator(0, 10);
/**
 * @var \Bitrix\Main\Validation\ValidationResult $validateResult
 */
$validateResult = $validator->validate('ваше валидируемое значение');
/**
 * @var bool $isSuccess
 */
$isSuccess = $validateResult->isSuccess(); // успешна ли валидация
/**
 * @var \Bitrix\Main\Error[] $errors
 */
$errors = $validateResult->getErrors();
```

Валидация массива:
Вообще валидировать массив нельзя, вы должны создавать объект DTO на основе массива и его свойства валидировать с помощью атрибутов.

Вы получаете массив:
```php
/**
 * условные данные с реквеста или просто какойто массив
 */
$data = [
    'name' => 'имя',
    'title' => 'строк в названии',
    'price' => 2800
];
```

И создаете на его основе свой класс, в своем пространстве имен, и с своим названием, условно DataDTO:

```php
namespace Your\Namespace;
use Bitrix\Main\Validation\Rule\Length;
use Bitrix\Main\Validation\Rule\PositiveNumber;
class DataDTO
{
    public function __construct(
        #[Length(1,33)]
        public readonly string $name,
        public readonly string $title,
        #[PositiveNumber('Цена должна быть больше 0')]
        public readonly float $price
    ){}
}
```

В этом объекте в конструкторе инициализируете свойства и вешаете на них атрибуты, они то и отвечают за валидацию. Кстати здесь в PositiveNumber я передал кастомное сообщение об ошибке.

Ну а после этого можете создать объект этого класса и выполнить валидацию:

```php
$dto = new DataDTO($data['name'], $data['title'], $data['price']);
/**
 * @var Bitrix\Main\Validation\ValidationService $serviceValidation
 */
$serviceValidation = new ValidationService();
/**
 * @var \Bitrix\Main\Validation\ValidationResult $validateResult
 */
$resultValidate = $serviceValidation->validate($dto);
/**
 * @var bool $isSuccess
 */
$validateSuccess = $resultValidate->isSuccess(); // успешна ли валидация
/**
 * @var \Bitrix\Main\Error[] $errors
 */
$validateErrors = $resultValidate->getErrors();
```

Ну и кастомные валидаторы и атрибуты:

Создаете в своем пространстве имен класс который будет отвечать за валидацию, он должен реализовывать интерфейс \Bitrix\Main\Validation\Validator\ValidatorInterface.

Атрибуты должны наследоваться от класса AbstractPropertyValidationAttribute. Последним параметром всегда можно указывать $errorMessage для кастомных сообщений об ошибке, битрикс будет его использовать если оно не null. Остальные первые параметры должны быть такие же как и в вашем валидаторе. Так же должен быть реализован метод getValidators, он возвращает массив объектов валидаторов которые будут использоваться при использовании атрибута над свойством класса.

```php
/**
 * кастомное правило валидации
 */
$validator = new ContainsValidator('строка');
$validateResult = $validator->validate('в этой строке будет поиск слова - строка');
```
И так же можно добавить свой атрибут в DataDTO
```php
class DataDTO
{
    public function __construct(
        #[Length(1,33)]
        public readonly string $name,
        #[ContainsRule('строка', 'кастомное сообщение')]
        public readonly string $title,
        #[PositiveNumber('Цена должна быть больше 0')]
        public readonly float $price
    ){}
}
```