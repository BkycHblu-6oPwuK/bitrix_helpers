<?php

namespace Itb\Form;

use Bitrix\Main\Context;
use Bitrix\Main\Web\Uri;
use Itb\Main\PageHelper;

class FormNewBuilder
{
    protected array $arResult;

    public function __construct(array $arResult)
    {
        $this->arResult = $arResult;
    }

    public function build(): FormNewDTO
    {
        $context = Context::getCurrent();
        $dto = new FormNewDTO();
        $dto->id = (int)$this->arResult['arForm']['ID'];
        $dto->title = $this->arResult['FORM_TITLE'] ?? '';
        $dto->description = $this->arResult['FORM_DESCRIPTION'] ?? '';
        $dto->error = $this->arResult['FORM_ERRORS'] && !is_array($this->arResult['FORM_ERRORS']) ? $this->arResult['FORM_ERRORS'] : '';
        $dto->url = $context->getServer()->getRequestUri();
        $dto->successAdded = $context->getRequest()->get('formresult') === 'addok';
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
            $answer = $this->arResult['arAnswers'][$sid][0] ?? null;
            if (!$answer) {
                continue;
            }

            $field = new FormNewFieldDTO();
            $field->id = (int)$answer['ID'];
            $field->name = $sid;
            $field->label = $question['TITLE'];
            $field->required = $question['REQUIRED'] === 'Y';
            $field->type = $answer['FIELD_TYPE'] ?: 'text';
            $field->attributes = $this->parseFieldParams($answer['FIELD_PARAM'] ?? '');
            $field->error = $this->arResult['FORM_ERRORS'] && is_array($this->arResult['FORM_ERRORS']) ? $this->arResult['FORM_ERRORS'][$sid] ?? '' : '';

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
            $fieldsMap[$field->name] = "form_{$field->type}_{$field->id}";
        }
        return array_merge($fieldsMap, [
            'ajax' => 'AJAX_CALL',
            'formId' => 'WEB_FORM_ID',
            'formApply' => 'web_form_apply',
        ]);
    }
}
