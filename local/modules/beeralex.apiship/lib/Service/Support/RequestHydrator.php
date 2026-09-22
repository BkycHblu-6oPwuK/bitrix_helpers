<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Service\Support;

/**
 * Заполняет объекты SDK (Request/Part) из ассоциативных массивов через fluent-сеттеры
 * (setXxx). Используется вместо прямого присвоения public-свойств, т.к. часть объектов
 * SDK валидирует обязательные поля именно в геттерах/сеттерах.
 */
trait RequestHydrator
{
    /**
     * @template T of object
     * @param T $target
     * @param array<string,mixed> $data Ключи — camelCase-имена свойств.
     * @return T
     */
    protected function hydrate(object $target, array $data): object
    {
        foreach ($data as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $setter = 'set' . ucfirst($key);
            if (method_exists($target, $setter)) {
                $target->$setter($value);
            }
        }
        return $target;
    }
}
