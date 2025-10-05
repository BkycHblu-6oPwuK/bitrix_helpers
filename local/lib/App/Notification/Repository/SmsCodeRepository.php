<?php

namespace App\Notification\Repository;

use Bitrix\Main\Type\DateTime;
use App\User\Phone\PhoneFormatter;
use Itb\Core\Repository\BaseHighloadRepository;

class SmsCodeRepository extends BaseHighloadRepository
{
    /** @var \Bitrix\Main\ORM\Data\DataManager|string */
    protected readonly string $entity;
    protected readonly PhoneFormatter $phoneFormatter;

    public function __construct()
    {
        parent::__construct('SmsBuilding');
        $this->phoneFormatter = new PhoneFormatter;
    }

    public function add(array $data): int
    {
        $data['UF_PHONE'] = $this->phoneFormatter->formatForDb($data['UF_PHONE']);
        return parent::add($data);
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
    public function update(int $id, array $data): void
    {
        $data['UF_PHONE'] = $this->phoneFormatter->formatForDb($data['UF_PHONE']);
        parent::update($id, $data);
    }

    /**
     * @throws \Exception
     */
    public function updateByPhone(array $data)
    {
        $id = $this->getByPhone($data['UF_PHONE'])['ID'];
        return $this->update($id, $data);
    }
}
