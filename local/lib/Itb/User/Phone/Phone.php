<?php

namespace Itb\User\Phone;

class Phone
{

    /**
     * @var string
     */
    private $number;
    private $formatted;


    /**
     * Phone constructor.
     *
     * @param string $number
     * @param string $code
     * @param string $countryName
     */
    public function __construct(string $number)
    {
        $formatter = new PhoneFormatter;
        $this->number = $formatter->formatForDb($number);
        $this->formatted = $formatter->formatForSite($number);
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number ?: '';
    }

    public function getFormatted() : string
    {
        return $this->formatted ?: '';
    }
}
