<?php
namespace Beeralex\Api\Domain\Checkout;

use Beeralex\Api\Domain\Checkout\DTO\PropertyDTO;
use Bitrix\Sale\PropertyValueCollectionBase;

class PropertiesBuilder
{
    public function __construct(private PropertyValueCollectionBase $orderProperties, private int $personTypeId) {}

    public function build(): array
    {
        $properties = [];
        foreach ($this->orderProperties as $propertyValue) {
            $property = $propertyValue->getProperty();
            if ((int)$property['PERSON_TYPE_ID'] === $this->personTypeId) {
                $properties[] = PropertyDTO::make([
                    'id' => $property['ID'],
                    'code' => $property['CODE'],
                    'name' => $property['NAME'],
                    'value' => $propertyValue->getValue(),
                    'type' => $property['TYPE'],
                    'required' => $property['REQUIRED'] === 'Y',
                    'pattern' => $property['PATTERN'] ?? '',
                    'isZip' => $property['IS_ZIP'] === 'Y',
                    'isLocation' => $property['IS_LOCATION'] === 'Y',
                    'isPhone' => $property['IS_PHONE'] === 'Y',
                    'isEmail' => $property['IS_EMAIL'] === 'Y',
                    'IsAddress' => $property['IS_ADDRESS'] === 'Y',
                    'minLength' => (int)$property['MINLENGTH'],
                    'maxLength' => (int)$property['MAXLENGTH'],
                    'multiple' => $property['MULTIPLE'] === 'Y',
                ]);
            }
        }
        return $properties;
    }

    public function buildIdsMap(): array
    {
        foreach ($this->orderProperties as $propertyValue) {
            $property = $propertyValue->getProperty();
            if ((int)$property['PERSON_TYPE_ID'] === $this->personTypeId) {
                $map[$property['CODE']] = 'ORDER_PROP_' . $property['ID'];
            }
        }
        return $map ?? [];
    }
}
