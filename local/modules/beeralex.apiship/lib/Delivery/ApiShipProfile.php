<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Delivery;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Профиль службы доставки ApiShip (гибридная схема, docs/DECISIONS.md ADR-004).
 *
 * Профиль — это "служба доставки" под конкретную ТК/тариф ApiShip: наследует всю логику
 * расчёта/CONFIG у ApiShipHandler, но CONFIG.MAIN.PROVIDER_KEY/TARIFF_ID заполняются под
 * конкретную ТК. Список реальных ТК подтягивается динамически из lists/providers
 * (см. Contracts\ProviderServiceContract) — профили в Bitrix создаются администратором
 * вручную под нужные ТК, без необходимости писать новый PHP-класс на каждую службу.
 */
class ApiShipProfile extends ApiShipHandler
{
    protected static $isProfile = true;

    public static function isProfile(): bool
    {
        return true;
    }

    public static function canHasProfiles(): bool
    {
        return false;
    }

    public function getProfilesList(): array
    {
        return [];
    }

    protected function getProfileType(): string
    {
        $config = $this->getConfigValues()['MAIN'] ?? [];
        return (string)($config['PROVIDER_KEY'] ?? '');
    }

    public static function getClassTitle(): string
    {
        return Loc::getMessage('BEERALEX_APISHIP_PROFILE_TITLE') ?: 'ApiShip: тариф';
    }

    public static function getClassDescription(): string
    {
        return Loc::getMessage('BEERALEX_APISHIP_PROFILE_DESCRIPTION')
            ?: 'Профиль конкретной ТК/тарифа ApiShip. Укажите providerKey и tariffId из личного кабинета ApiShip.';
    }
}
