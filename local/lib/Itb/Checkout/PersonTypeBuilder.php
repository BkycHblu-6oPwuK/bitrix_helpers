<?php

namespace Itb\Checkout;

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

    public function build(): array
    {
        $result = [
            'fields' => $this->personType->mapWithKeys(function ($person) {
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
                $dto = new PersonTypeDTO;
                $dto->name = $name;
                $dto->checked = $person['CHECKED'] === 'Y';
                return [
                    $this->personTypeMap[$person['CODE']] => $dto
                ];
            }),
            'selected' => $this->selectedPersonType,
            'oldPersonType' => $this->selectedPersonType
        ];
        return $result;
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
