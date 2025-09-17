<?php

namespace Itb\Highloadblock\Repository;

use Bitrix\Main\Type\DateTime;
use Itb\Core\Helpers\HlblockHelper;
use Itb\User\Phone\PhoneFormatter;

class SmsCodeRepository
{
    /** @var \Bitrix\Main\ORM\Data\DataManager|string */
    protected readonly string $entity;
    protected readonly PhoneFormatter $phoneFormatter;

    public function __construct()
    {
        /**
         * хайлоад для смс с кодами для авторизации
         */
        $this->entity = HlblockHelper::getHlblockByName('SmsBuilding');
        $this->phoneFormatter = new PhoneFormatter;
    }

    /**
     * @throws \Exception
     */
    public function add(array $fields)
    {
        $fields['UF_PHONE'] = $this->phoneFormatter->formatForDb($fields['UF_PHONE']);
        $result = $this->entity::add($fields);
        if (!$result->isSuccess()) {
            throw new \Exception();
        }
        return $result;
    }

    /**
     * @param int $timeLimit ограничение в минутах, после чего код устаревает
     * @throws \Exception
     */
    public function getByPhone(string $phone, int $minutesLimit = 10)
    {
        $phone = $this->phoneFormatter->formatForDb($phone);
        $query = $this->entity::query()
            ->setSelect(['*'])
            ->where('UF_PHONE', $phone)
            ->where('UF_NUMBER_INPUT', '<=', 2)
            ->where('UF_ACTIVE', 1)
            ->setOrder(['UF_DATE_CREATE' => 'DESC'])
            ->setLimit(1);
        if ($minutesLimit > 0) {
            $timeLimit = (new DateTime())->add("-{$minutesLimit} minutes");
            $query = $query->where('UF_DATE_CREATE', '>=', $timeLimit);
        }

        $result = $query->fetch();

        if (!$result) throw new \Exception('Не найдено поле для обновления');
        return $result;
    }

    /**
     * @throws \Exception
     */
    public function update(int $id, array $fields)
    {
        $fields['UF_PHONE'] = $this->phoneFormatter->formatForDb($fields['UF_PHONE']);
        $result = $this->entity::update($id, $fields);
        if (!$result->isSuccess()) {
            throw new \Exception();
        }
        return $result;
    }

    /**
     * @throws \Exception
     */
    public function updateByPhone(array $fields)
    {
        $id = $this->getByPhone($fields['UF_PHONE'])['ID'];
        return $this->update($id, $fields);
    }
}
