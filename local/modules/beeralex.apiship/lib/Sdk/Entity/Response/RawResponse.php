<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Sdk\Entity\Response;

use Apiship\Entity\AbstractResponse;

/**
 * Универсальный ответ для расширенных методов SDK.
 *
 * В отличие от штатных Response SDK (которые требуют геттер/сеттер под каждое поле),
 * складывает произвольные поля JSON в ассоциативный массив и отдаёт их через
 * магический доступ и toArray(). Удобно для методов, где строгая типизация ответа
 * не нужна (webhooks, connections, lists/statuses, tariffs).
 */
class RawResponse extends AbstractResponse
{
    /** @var array<string,mixed> */
    protected array $data = [];

    /**
     * Заполняет ответ из декодированного JSON (объект/массив).
     *
     * @param mixed $decoded stdClass|array
     */
    public function fill(mixed $decoded): static
    {
        $this->data = self::normalize($decoded);
        return $this;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return $this->data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Рекурсивно приводит stdClass к массиву.
     *
     * @return array<string,mixed>
     */
    private static function normalize(mixed $value): array
    {
        $value = json_decode(json_encode($value), true);
        return is_array($value) ? $value : [];
    }
}
