<?

namespace Itb\Notification\Contracts;

use Itb\User\Phone\Phone;

interface SmsCodeContract
{
    public function sendCode(Phone $phone) : void;
}