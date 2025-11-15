<?
declare(strict_types=1);
namespace Beeralex\User\Contracts;

use Beeralex\User\Contracts\UserEntityContract;

interface UserFactoryContract
{
    public function create(array $fields): UserEntityContract;
}
