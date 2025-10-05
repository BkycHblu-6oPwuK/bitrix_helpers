<?

namespace App\Notification\Contracts;

use App\User\Phone\Phone;

interface SmsCodeContract
{
    public function sendCode(Phone $phone) : void;
}