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
        $dto = new FormNewDTO();
        $dto->id = (int)$this->arResult['arForm']['ID'];
        $dto->title = $this->arResult['FORM_TITLE'] ?? '';
        $dto->description = $this->arResult['FORM_DESCRIPTION'] ?? '';
        $dto->error = $this->arResult['FORM_ERRORS'] && !is_array($this->arResult['FORM_ERRORS']) ? $this->arResult['FORM_ERRORS'] : '';
        $dto->successAdded = $this->arResult['SUCCESS'] === true;
        $dto->fields = $this->buildFields();
        $dto->formIdsMap = $this->buildFormIdsMap($dto->fields);

        return $dto;
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
            $field = new FormNewFieldDTO();
            $field->id = (int)$firstAnswer['ID'];
            $field->name = $sid;
            $field->label = $question['TITLE'];
            $field->required = $question['REQUIRED'] === 'Y';
            $field->type = $firstAnswer['FIELD_TYPE'] ?: 'text';
            $field->isMultiple = in_array($firstAnswer['FIELD_TYPE'], ['multiselect', 'checkbox']);
            $field->attributes = $this->parseFieldParams($firstAnswer['FIELD_PARAM'] ?? '');
            $field->error = $this->arResult['FORM_ERRORS'] && is_array($this->arResult['FORM_ERRORS'])
                ? $this->arResult['FORM_ERRORS'][$sid] ?? ''
                : '';

            if (count($answers) > 1) {
                $field->options = [];
                foreach ($answers as $answer) {
                    $field->options[] = [
                        'id' => (int)$answer['ID'],
                        'label' => $answer['MESSAGE'],
                        'value' => $answer['VALUE'] ?: (string)$answer['ID'],
                        'sort' => (int)$answer['C_SORT'],
                        'checked' => $answer['FIELD_PARAM'] === 'checked',
                        'width' => (int)$answer['WIDTH'],
                        'height' => (int)$answer['HEIGH'],
                        'checked' => $answer['FIELD_PARAM'] === 'checked',
                        'active' => $answer['ACTIVE'] === 'Y',
                    ];
                }
            }

            $fields[] = $field;
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
