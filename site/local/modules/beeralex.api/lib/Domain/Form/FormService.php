<?

declare(strict_types=1);

namespace Beeralex\Api\Domain\Form;

use Beeralex\Api\ApiResult;
use Bitrix\Main\Result;
use Bitrix\Main\Error;

class FormService
{
    public function __construct(
        protected readonly FormRepository $formRepository,
        protected readonly FormAnswerRepository $formAnswerRepository
    ) {}

    /**
     * Более монолитный метод для отправки формы вопросов, но фронту удобно
     */
    /*
    public function submitQuestionForm(Dto\QuestionRequestDTO $requestDto): Result
    {
        $formId = $this->getFormIdByName('Вопросы');
        $mapFields = $this->getFormFieldMapByTitles($formId, [
            'Имя',
            'Телефон',
            'текст',
        ]);
        $this->setInGlobals([
            'WEB_FORM_ID' => $formId,
            'web_form_apply' => 'Y',
            $this->buildFormKey($mapFields['Имя']) => $requestDto->name,
            $this->buildFormKey($mapFields['Телефон']) => $requestDto->phone,
            $this->buildFormKey($mapFields['текст']) => $requestDto->text,
        ]);
        service(\Beeralex\Core\Service\FileService::class)->includeFile('v1.form.index', [
            'formId' => $formId
        ]);
        return $this->processErrors();
    }*/

    protected function getFormIdByName(string $name): int
    {
        $formId = $this->formRepository->getIdByName($name);
        if ($formId === null) {
            throw new \RuntimeException('Form not found');
        }
        return $formId;
    }

    protected function getFormFieldMapByTitles(int $formId, array $titles): array
    {
        $fieldIds = $this->formAnswerRepository->getMapByTitles($formId, $titles);
        if ($fieldIds === null) {
            throw new \RuntimeException('Form fields not found');
        }
        return $fieldIds;
    }

    protected function processErrors(): Result
    {
        $apiResult = service(ApiResult::class);
        $result = new Result;
        /**
         * @var array $formData - from Dto\FormNewDTO
         */
        $formData = $apiResult->getData();
        if ($formData['error']) {
            $result->addError(new Error($formData['error'], 'form'));
        }
        foreach ($formData['fields'] as $field) {
            if (!empty($field['error'])) {
                $result->addError(new Error($field['error'], $field['name']));
            }
        }
        if ($result->isSuccess() && !$formData['successAdded']) {
            $result->addError(new Error('Form not submitted', 'form'));
        }
        return $result;
    }

    protected function buildFormKey(?array $field): string
    {
        if ($field === null) {
            throw new \RuntimeException('Form field not found');
        }
        $result = '';
        if (in_array($field['TYPE'], ['multiselect', 'checkbox'], true)) {
            $result = "form_{$field['TYPE']}_{$field['SID']}[]";
        } elseif (in_array($field['TYPE'], ['dropdown', 'radio'], true)) {
            $result = "form_{$field['TYPE']}_{$field['SID']}";
        } else {
            $result = "form_{$field['TYPE']}_{$field['ID']}";
        }
        return $result;
    }

    protected function setInGlobals(array $data): void
    {
        foreach ($data as $key => $value) {
            if (empty($value) || $key === '') {
                continue;
            }
            $_POST[$key] = $value;
            $_REQUEST[$key] = $value;
            $_FILES[$key] = $value;
        }
    }
}
