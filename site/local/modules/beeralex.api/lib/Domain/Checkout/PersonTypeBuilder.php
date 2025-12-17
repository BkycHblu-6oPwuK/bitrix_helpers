<?php
namespace Beeralex\Api\Domain\Checkout;

use Beeralex\Api\Domain\Checkout\DTO\PersonTypeDTO;

class PersonTypeBuilder
{
    private $personTypeMap = [
        'physical' => 'physical',
        'legal' => 'legal',
    ];

    /**
     * @var \Illuminate\Support\Collection from sale.order.ajax
     */
    private $personType;

    /**
     * @var string
     */
    private $selectedPersonType;

    public function __construct(array $personType, int $selectedPersonType)
    {
        $this->personType = collect($personType)->filter(fn($person) => $this->personTypeMap[$person['CODE']] ?? false);
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
                $this->personTypeMap[$person['CODE']] => [
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
            $this->personTypeMap[$person['CODE']] ?? '' => (int)$person['ID']
        ])->toArray();
    }

    protected function getPersonDTOKey(int $id): string
    {
        $person = $this->personType->first(fn($person) => $person['ID'] == $id);
        if(!$person['CODE']){
            $person = $this->personType->first();
        }
        return $this->personTypeMap[$person['CODE'] ?? ''] ?? '';
    }

}
