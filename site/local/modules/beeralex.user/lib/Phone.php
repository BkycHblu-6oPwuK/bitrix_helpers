<?php
declare(strict_types=1);
namespace Beeralex\User;

use Bitrix\Main\PhoneNumber\Parser;
use Bitrix\Main\PhoneNumber\Formatter;
use Bitrix\Main\PhoneNumber\Format;
use Bitrix\Main\PhoneNumber\PhoneNumber;
use Bitrix\Main\PhoneNumber\ShortNumberFormatter;

class Phone
{
    public readonly PhoneNumber $phoneNumber;

    private function __construct(PhoneNumber $phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    public static function fromString(string $number, string $defaultCountry = ''): self
    {
        $phoneNumber = Parser::getInstance()->parse($number, $defaultCountry);
        return new static($phoneNumber);
    }

    public static function normalizeE164(string $number, string $defaultCountry = ''): ?string
    {
        $p = static::fromString($number, $defaultCountry);
        return $p->isValid() ? $p->formatE164() : null;
    }

    public function isValid(): bool
    {
        return $this->phoneNumber->isValid();
    }

    public function getRaw(): string
    {
        return $this->phoneNumber->getRawNumber();
    }

    public function getCountry(): string
    {
        return (string)$this->phoneNumber->getCountry();
    }

    public function getCountryCode(): string
    {
        return (string)$this->phoneNumber->getCountryCode();
    }

    public function getNationalNumber(): string
    {
        return (string)$this->phoneNumber->getNationalNumber();
    }

    public function hasPlus(): bool
    {
        return $this->phoneNumber->hasPlus();
    }

    public function hasExtension(): bool
    {
        return $this->phoneNumber->hasExtension();
    }

    public function getExtension(): string
    {
        return (string)$this->phoneNumber->getExtension();
    }

    public function formatE164(): string
    {
        return Formatter::format($this->phoneNumber, Format::E164);
    }

    public function formatInternational(): string
    {
        return Formatter::format($this->phoneNumber, Format::INTERNATIONAL);
    }

    public function formatNational(bool $forceNationalPrefix = false): string
    {
        return Formatter::format($this->phoneNumber, Format::NATIONAL, $forceNationalPrefix);
    }

    public function formatOriginal(): string
    {
        return Formatter::formatOriginal($this->phoneNumber);
    }

    public function prettyIfShort(): string
    {
        if (!$this->phoneNumber->isValid() && ShortNumberFormatter::isApplicable($this->phoneNumber)) {
            return ShortNumberFormatter::format($this->phoneNumber);
        }
        return $this->getRaw();
    }

    /**
     * Простое маскирование номера (оставляет visibleCount последних цифр)
     */
    public function mask(int $visibleCount = 4, string $maskChar = '*'): string
    {
        $digits = preg_replace("/\D+/", "", $this->getRaw());
        $len = mb_strlen($digits);
        if ($len <= $visibleCount) {
            return str_repeat($maskChar, $len);
        }
        $prefix = mb_substr($digits, 0, $len - $visibleCount);
        $visible = mb_substr($digits, -$visibleCount);
        return str_repeat($maskChar, mb_strlen($prefix)) . $visible;
    }

    public function asString(): string
    {
        if ($this->isValid()) {
            return $this->formatE164();
        }
        return $this->getRaw();
    }
}