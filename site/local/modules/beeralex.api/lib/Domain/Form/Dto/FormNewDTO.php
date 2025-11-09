<?php
declare(strict_types=1);

namespace Beeralex\Api\Domain\Form\Dto;

class FormNewDTO
{
    public int $id = 0;
    public string $url;
    public string $title = '';
    public string $description = '';
    /** @var FormNewFieldDTO[] */
    public array $fields = [];
    public array $formIdsMap;
    public string $error = '';
    public bool $successAdded = false;
}
