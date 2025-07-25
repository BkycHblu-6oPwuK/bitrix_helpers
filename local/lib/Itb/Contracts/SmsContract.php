<?

namespace Itb\Contracts;

use Itb\User\Phone\Phone;

interface SmsContract 
{
    public function sendSms(Phone $phone, string $message) : array;
}