<?php

declare(strict_types=1);

namespace Beeralex\Api\Domain\Form;

use Beeralex\Api\Domain\Form\Dto\FormNewDTO;
use Beeralex\Api\Domain\Form\Dto\FormNewFieldDTO;

class FormNewBuilder
{
    protected array $arResult;

    public function __construct(array $arResult)
    {
        $this->arResult = $arResult;
    }

    public function build(): FormNewDTO
    {
        $fields = $this->buildFields();
        return FormNewDTO::make([
            'id' => (int)($this->arResult['arForm']['ID'] ?? 0),
            'title' => $this->arResult['FORM_TITLE'] ?? '',
            'description' => $this->arResult['FORM_DESCRIPTION'] ?? '',
            'error' => ($this->arResult['FORM_ERRORS'] && !is_array($this->arResult['FORM_ERRORS'])) ? $this->arResult['FORM_ERRORS'] : '',
            'successAdded' => ($this->arResult['SUCCESS'] ?? null) === true,
            'fields' => $fields,
            'formIdsMap' => $this->buildFormIdsMap($fields),
        ]);
    }

    /**
     * @return FormNewFieldDTO[]
     */
    protected function buildFields(): array
    {
        $fields = [];

        foreach ($this->arResult['arQuestions'] ?? [] as $sid => $question) {
            $answers = $this->arResult['arAnswers'][$sid] ?? [];
            if (empty($answers)) {
                continue;
            }

            $firstAnswer = $answers[0];

            $options = [];
            if (count($answers) > 1) {
                foreach ($answers as $answer) {
                    $options[] = [
                        'id' => (int)$answer['ID'],
                        'label' => $answer['MESSAGE'],
                        'value' => $answer['VALUE'] ?: (string)$answer['ID'],
                        'sort' => (int)$answer['C_SORT'],
                        'checked' => ($answer['FIELD_PARAM'] ?? '') === 'checked',
                        'width' => (int)($answer['WIDTH'] ?? 0),
                        'height' => (int)($answer['HEIGH'] ?? 0),
                        'active' => ($answer['ACTIVE'] ?? 'N') === 'Y',
                    ];
                }
            }

            $fields[] = FormNewFieldDTO::make([
                'id' => (int)$firstAnswer['ID'],
                'name' => $sid,
                'label' => $question['TITLE'],
                'required' => ($question['REQUIRED'] ?? 'N') === 'Y',
                'type' => $firstAnswer['FIELD_TYPE'] ?: 'text',
                'isMultiple' => in_array($firstAnswer['FIELD_TYPE'], ['multiselect', 'checkbox']),
                'attributes' => $this->parseFieldParams($firstAnswer['FIELD_PARAM'] ?? ''),
                'error' => ($this->arResult['FORM_ERRORS'] && is_array($this->arResult['FORM_ERRORS']))
                    ? ($this->arResult['FORM_ERRORS'][$sid] ?? '')
                    : '',
                'options' => $options,
            ]);
        }

        return $fields;
    }

    protected function parseFieldParams(string $fieldParam): array
    {
        $result = [];
        if (preg_match_all('/([\w\-]+)=["\']?([^"\']+)["\']?/', $fieldParam, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $result[$match[1]] = $match[2];
            }
        }
        return $result;
    }


    /**
     * @param FormNewFieldDTO[] $fields
     */
    protected function buildFormIdsMap(array $fields): array
    {
        $fieldsMap = [];

        foreach ($fields as $field) {
            if (in_array($field->type, ['dropdown', 'multiselect', 'checkbox', 'radio'], true)) {
                $fieldsMap[$field->name] = "form_{$field->type}_{$field->name}";
            } else {
                $fieldsMap[$field->name] = "form_{$field->type}_{$field->id}";
            }
        }

        return $fieldsMap;
    }
}
