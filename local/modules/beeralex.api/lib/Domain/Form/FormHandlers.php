<?

declare(strict_types=1);

namespace Beeralex\Api\Domain\Form;

use Bitrix\Main\Result;

/**
 * Обработчики форм под конкретный сайт
 */
class FormHandlers
{
    public function __construct(
        protected readonly FormService $formService,
    ) {}

    /**
     * Более монолитный метод для отправки формы вопросов, но фронту удобно
     */
    public function submitQuestionForm(Dto\QuestionRequestDTO $requestDto): Result
    {
        $formId = $this->formService->getFormIdByName('Вопросы');
        $mapFields = $this->formService->getFormFieldMapByTitles($formId, [
            'Имя',
            'Телефон',
            'текст',
        ]);
        $this->formService->setInGlobals([
            'WEB_FORM_ID' => $formId,
            'web_form_apply' => 'Y',
            $this->formService->buildFormKey($mapFields['Имя']) => $requestDto->name,
            $this->formService->buildFormKey($mapFields['Телефон']) => $requestDto->phone,
            $this->formService->buildFormKey($mapFields['текст']) => $requestDto->text,
        ]);
        service(\Beeralex\Core\Service\FileService::class)->includeFile('v1.form.index', [
            'formId' => $formId
        ]);
        return $this->formService->processErrors();
    }

    public function submitPartnersProgramForm(Dto\PartnersProgramRequestDTO $requestDto): Result
    {
        $formId = $this->formService->getFormIdByName('Заявка на программу СТО');
        $mapFields = $this->formService->getFormFieldMapByTitles($formId, [
            'Имя',
            'Фактический адрес',
            'Название компании',
            'Номер телефона',
            'E-mail',
            'Web-сайт',
            'Ф.И.О. контактного лица',
            'файл',
        ]);
        $this->formService->setInGlobals([
            'WEB_FORM_ID' => $formId,
            'web_form_apply' => 'Y',
            $this->formService->buildFormKey($mapFields['Имя']) => $requestDto->name,
            $this->formService->buildFormKey($mapFields['Фактический адрес']) => $requestDto->actualAddress,
            $this->formService->buildFormKey($mapFields['Название компании']) => $requestDto->nameCompany,
            $this->formService->buildFormKey($mapFields['Номер телефона']) => $requestDto->phone,
            $this->formService->buildFormKey($mapFields['E-mail']) => $requestDto->email,
            $this->formService->buildFormKey($mapFields['Web-сайт']) => $requestDto->website,
            $this->formService->buildFormKey($mapFields['Ф.И.О. контактного лица']) => $requestDto->fioContactPerson,
        ]);
        service(\Beeralex\Core\Service\FileService::class)->includeFile('v1.form.index', [
            'formId' => $formId
        ]);
        return $this->formService->processErrors();
    }

    public function submitVacancyForm(Dto\VacancyRequestDTO $requestDto): Result
    {
        $formId = $this->formService->getFormIdByName('Отклики на вакансии');
        $mapFields = $this->formService->getFormFieldMapByTitles($formId, [
            'Имя',
            'Телефон',
            'E-mail',
            'Резюме',
            'id вакансии',
        ]);
        $this->formService->setInGlobals([
            'WEB_FORM_ID' => $formId,
            'web_form_apply' => 'Y',
            $this->formService->buildFormKey($mapFields['Имя']) => $requestDto->name,
            $this->formService->buildFormKey($mapFields['Телефон']) => $requestDto->phone,
            $this->formService->buildFormKey($mapFields['E-mail']) => $requestDto->email,
            $this->formService->buildFormKey($mapFields['Резюме']) => $requestDto->resumeFile,
            $this->formService->buildFormKey($mapFields['id вакансии']) => $requestDto->idVacancy,
        ]);
        service(\Beeralex\Core\Service\FileService::class)->includeFile('v1.form.index', [
            'formId' => $formId
        ]);
        return $this->formService->processErrors();
    }
}
