<?php
namespace Itb\Notification\Services\Sms;

use Itb\Notification\Contracts\SmsCodeContract;
use Itb\Notification\Contracts\SmsContract;
use Itb\Core\Helpers\WebHelper;
use Itb\Notification\Exceptions\SmsException;
use Itb\Notification\Enum\NotificationStatuses;
use Itb\Notification\SmsTemplates;
use Itb\Highloadblock\Repository\SmsCodeRepository;
use Itb\User\Phone\Phone;

class SmsCodeService implements SmsCodeContract
{
    protected readonly SmsCodeRepository $repository;
    protected readonly SmsContract $smsService;
    
    public function __construct(SmsContract $smsService)
    {
        $this->smsService = $smsService;
        $this->repository = new SmsCodeRepository;
    }

    /**
     * @throws SmsException
     * @throws \Exception
     */
    public function sendCode(Phone $phone) : void
    {
        $code = WebHelper::generateCode(4);
        $this->smsService->sendSms($phone, SmsTemplates::get(NotificationStatuses::SMS_CODE, '#CODE#', $code));
        $fields = [
            'UF_PHONE' => $phone->getNumber(),
            'UF_SMS' => $code
        ];
        $this->repository->add($fields);
    }

    public function checkCode(Phone $phone, int $code) : bool
    {
        $data = $this->repository->getByPhone($phone->getNumber());
        $data['UF_NUMBER_INPUT'] = (int)$data['UF_NUMBER_INPUT'] + 1;
        $fields = [
            'UF_PHONE' => $phone->getNumber(),
            'UF_NUMBER_INPUT' => $data['UF_NUMBER_INPUT'],
            'UF_ACTIVE' => $data['UF_NUMBER_INPUT'] <= 2 ? 1 : 0
        ];
        $this->repository->updateByPhone($fields);
        return (int)$data['UF_SMS'] === $code;
    }
}
