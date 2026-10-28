<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Controllers;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Web\Json;
use Beeralex\Apiship\Contracts\WebhookServiceContract;
use Beeralex\Apiship\Dto\WebhookEventDto;

/**
 * Приём входящих вебхуков ApiShip (server-to-server).
 * CSRF/сессионные prefilters отключены — эндпоинт публичный, вызывается ApiShip.
 * При ошибке обработки возвращает 5xx: ApiShip повторит запрос (3 попытки, таймаут 30 с).
 */
class WebhookController extends Controller
{
    public function configureActions(): array
    {
        return [
            'handle' => ['prefilters' => []],
        ];
    }

    /**
     * @return array<string,mixed>|null
     */
    public function handleAction(): ?array
    {
        $rawBody = (string)$this->getRequest()->getInput();

        try {
            $decoded = Json::decode($rawBody) ?: [];
        } catch (\Throwable $e) {
            // Невалидный JSON — ретраить бессмысленно, возвращаем 400
            $this->addError(new Error('Invalid JSON body'));
            return null;
        }

        // ApiShip может присылать как один объект события, так и массив
        $events = isset($decoded[0]) ? $decoded : [$decoded];

        $processed = 0;
        $duplicates = 0;

        $service = \service(WebhookServiceContract::class);

        foreach ($events as $eventData) {
            if (!is_array($eventData)) {
                continue;
            }

            try {
                $isNew = $service->handle(
                    WebhookEventDto::make($eventData),
                    Json::encode($eventData)
                );
                $isNew ? $processed++ : $duplicates++;
            } catch (\Throwable $e) {
                \AddMessage2Log(
                    'ApiShip: ошибка при обработке вебхука: ' . $e->getMessage(),
                    'beeralex.apiship'
                );
                // 5xx → ApiShip повторит запрос
                \Bitrix\Main\Context::getCurrent()->getResponse()->setStatus('500 Internal Server Error');
                $this->addError(new Error('Internal Server Error'));
                return null;
            }
        }

        return [
            'processed'  => $processed,
            'duplicates' => $duplicates,
        ];
    }
}
