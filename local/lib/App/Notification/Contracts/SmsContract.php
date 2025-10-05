<?

namespace App\Notification\Contracts;

use App\User\Phone\Phone;

interface SmsContract 
{
    public function sendSms(Phone $phone, string $message) : array;
}