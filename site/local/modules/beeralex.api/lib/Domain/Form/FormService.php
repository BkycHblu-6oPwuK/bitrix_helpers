<?

declare(strict_types=1);

namespace Beeralex\Api\Domain\Form;

use Beeralex\Api\ApiResult;
use Bitrix\Main\Result;
use Bitrix\Main\Error;

class FormService
{
    public function __construct(
        protected readonly FormRepository $formRepository
    ) {}

    /**
     * Более монолитный метод для отправки формы вопросов, но фронту удобно
     */
    /*
    public function submitQuestionForm(Dto\QuestionRequestDTO $requestDto): Result
    {
        $formId = $this->getFormIdByName('Вопросы');
        $this->setInGlobals([
            'WEB_FORM_ID' => $formId,
            'web_form_apply' => 'Y',
            'form_text_1' => $requestDto->name,
            'form_text_2' => $requestDto->phone,
            'form_text_3' => $requestDto->text,
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
        if($result->isSuccess() && !$formData['successAdded']) {
            $result->addError(new Error('Form not submitted', 'form'));
        }
        return $result;
    }


    protected function setInGlobals(array $data): void
    {
        foreach ($data as $key => $value) {
            $_POST[$key] = $value;
            $_REQUEST[$key] = $value;
        }
    }
}
