<?

namespace Beeralex\User\Contracts;

use Beeralex\User\Contracts\UserEntityContract;
use Beeralex\User\Phone;

interface UserBuilderContract
{
    public function build(): UserEntityContract;
    public function setEmail(string $email): self;
    public function setName(string $name): self;
    public function setLastName(string $lastName): self;
    public function setPassword(string $password): self;
    public function setPhone(Phone $phone): self;
    public function setGroup(?array $group): self;
    public function setAuthentificatorKey(string $authKey): self;
}
