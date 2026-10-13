<?php
namespace Beeralex\Api\Domain\Checkout;

use Illuminate\Support\Collection;
use Beeralex\Api\Domain\Checkout\DTO\PersonTypeDTO;

class PersonTypeBuilder
{
    /** массив типов плательщиков из sale.order.ajax */
    private Collection $personType;
    private int $selectedPersonType;

    public function __construct(array $personType, int $selectedPersonType)
    {
        $this->personType = collect($personType);
        $this->selectedPersonType = $this->getPersonDTOKey($selectedPersonType);
    }

    public function build(): PersonTypeDTO
    {
        $types = $this->personType->mapWithKeys(function ($person) {
            $name = match($person['CODE']) {
                'physical' => [
                    'default' => 'Я, Физическое лицо',
                    'mobile' => 'Я, Физическое лицо',
                ],
                'legal' => [
                    'default' => 'Я, Юридическое лицо / ИП',
                    'mobile' => 'Я, Юр.лицо/ИП',
                ],
                default => [
                    'default' => $person['NAME'],
                    'mobile' => $person['NAME'],
                ]
            };

            return [
                $this->getKey($person) => [
                    'name' => $name,
                    'checked' => $person['CHECKED'] === 'Y',
                ]
            ];
        })->toArray();

        return PersonTypeDTO::make([
            'selected' => $this->selectedPersonType,
            'oldPersonType' => $this->selectedPersonType,
            'types' => $types,
        ]);
    }

    /**
     * @return array [dtoKey => paySystemId]
     */
    public function buildIdsMap(): array
    {
        return $this->personType->mapWithKeys(fn($person) => [
            $this->getKey($person) => (int)$person['ID']
        ])->toArray();
    }

    protected function getPersonDTOKey(int $id): string
    {
        $person = $this->personType->first(fn($person) => $person['ID'] == $id);
        if(!$person){
            $person = $this->personType->first();
        }
        return $this->getKey($person);
    }

    protected function getKey(array $person): string
    {
        return (string)($person['CODE'] ?: $person['ID']);
    }

}
