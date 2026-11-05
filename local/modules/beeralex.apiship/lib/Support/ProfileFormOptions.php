<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support;

use Beeralex\Apiship\Contracts\ConnectionServiceContract;
use Beeralex\Apiship\Contracts\ProviderServiceContract;
use Beeralex\Apiship\Enum\DeliveryType;

final class ProfileFormOptions
{
    /** @var array<int,array{id:string,providerKey:string,name:string}>|null */
    private ?array $connections = null;

    /** @var array<string,array<int,string>> ключ: providerKey|pickup|delivery */
    private array $tariffs = [];

    public function __construct(
        private readonly ConnectionServiceContract $connectionService,
        private readonly ProviderServiceContract $providerService,
    ) {}

    /** @return array<string,string> "{id}|{providerKey}" => название */
    public function connectionOptions(string $current): array
    {
        $options = ['' => '— выберите подключение —'];

        try {
            foreach ($this->loadConnections() as $conn) {
                $options["{$conn['id']}|{$conn['providerKey']}"] = "{$conn['name']} ({$conn['providerKey']})";
            }
        } catch (\Throwable $e) {
            $options[''] = 'Ошибка загрузки подключений: ' . $e->getMessage();
        }

        return $this->keepCurrent($options, [$current], 'Подключение %s (нет в ЛК ApiShip)');
    }

    /** @return array<int|string,string> */
    public function tariffOptions(ProfileConfig $config): array
    {
        $options = ['' => '— все тарифы ТК —'];

        if ($config->providerKey !== '') {
            try {
                $options += $this->loadTariffs(
                    $config->providerKey,
                    $config->pickupType ?: null,
                    $config->deliveryType ?: null
                );
            } catch (\Throwable) {
                // API недоступен: остаются «все тарифы», сохранённые значения вернёт keepCurrent
            }
        }

        return $this->keepCurrent($options, $config->tariffIds, 'Тариф #%s (нет в списке)');
    }

    /** @return array<int,array{id:string,providerKey:string,name:string}> */
    private function loadConnections(): array
    {
        if ($this->connections !== null) {
            return $this->connections;
        }

        $result = [];
        foreach ($this->connectionService->getConnections() as $conn) {
            $id = (string)($conn['id'] ?? '');
            $providerKey = (string)($conn['providerKey'] ?? '');
            if ($id === '' || $providerKey === '') {
                continue;
            }
            $result[] = [
                'id'          => $id,
                'providerKey' => $providerKey,
                'name'        => (string)($conn['name'] ?? $providerKey),
            ];
        }

        return $this->connections = $result;
    }

    /** @return array<int,string> id тарифа => «Название · До двери (#26)» */
    private function loadTariffs(string $providerKey, ?int $pickupType, ?int $deliveryType): array
    {
        $cacheKey = $providerKey . '|' . (int)$pickupType . '|' . (int)$deliveryType;
        if (isset($this->tariffs[$cacheKey])) {
            return $this->tariffs[$cacheKey];
        }

        $options = [];
        foreach ($this->providerService->getTariffs(limit: 999, filter: ['providerKey' => $providerKey]) as $row) {
            $id = (int)($row['id'] ?? 0);
            $name = trim((string)($row['name'] ?? ''));
            if ($id <= 0 || $name === '') {
                continue;
            }

            $rowDelivery = $this->intValues($row, 'deliveryTypes', 'deliveryType');
            $rowPickup = $this->intValues($row, 'pickupTypes', 'pickupType');

            // если API не вернул поле — тариф не прячем
            if ($pickupType !== null && $rowPickup !== [] && !in_array($pickupType, $rowPickup, true)) {
                continue;
            }
            if ($deliveryType !== null && $rowDelivery !== [] && !in_array($deliveryType, $rowDelivery, true)) {
                continue;
            }

            $typeNames = array_filter(array_map(
                static fn(int $t) => DeliveryType::tryFrom($t)?->label() ?? '',
                $rowDelivery
            ));
            $options[$id] = $name . ($typeNames ? ' · ' . implode(' / ', $typeNames) : '') . ' (#' . $id . ')';
        }
        asort($options, SORT_NATURAL | SORT_FLAG_CASE);

        return $this->tariffs[$cacheKey] = $options;
    }

    /** @return int[] */
    private function intValues(array $row, string $plural, string $single): array
    {
        return array_values(array_filter(array_map('intval', (array)($row[$plural] ?? $row[$single] ?? []))));
    }

    /**
     * Сохранённых значений нет в списке (сбой API, тариф убрали в ЛК) — добавляем,
     * иначе при следующем сохранении формы настройка молча сбросится.
     *
     * @param array<int|string,string> $options
     * @param array<int|string,mixed> $currentValues
     * @return array<int|string,string>
     */
    private function keepCurrent(array $options, array $currentValues, string $labelFormat): array
    {
        foreach ($currentValues as $current) {
            $current = (string)$current;
            if ($current !== '' && !isset($options[$current])) {
                $options[$current] = sprintf($labelFormat, $current);
            }
        }
        return $options;
    }
}
