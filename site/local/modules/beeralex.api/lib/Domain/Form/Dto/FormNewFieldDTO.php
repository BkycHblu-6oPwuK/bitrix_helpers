<?php
declare(strict_types=1);

namespace Beeralex\Api\Domain\Form\Dto;

class FormNewFieldDTO
{
    public int $id = 0;
    public string $name = '';
    public string $label = '';
    public string $type = '';
    public bool $required = false;
    public bool $isMultiple = false;
    public array $attributes = [];
    /** у формы параметр USE_EXTENDED_ERRORS должен быть = Y */
    public string $error = '';
    public array $options = []; // добавь это
}