<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Controllers;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Web\Json;
use Beeralex\Apiship\Contracts\WebhookServiceContract;
use Beeralex\Apiship\Dto\WebhookEventDto;
use Beeralex\Apiship\Options;

/**
 * Приём входящих вебхуков ApiShip (server-to-server, единственный HTTP-эндпоинт модуля —
 * фронт/Nuxt в это НЕ вовлечён, см. docs/ARCHITECTURE.md).
 *
 * CSRF/сессионные prefilters отключены (пустой массив в configureActions) — как и для
 * прочих публичных эндпоинтов проекта (см. beeralex.api контроллеры). Аутентификация
 * входящего запроса — по секрету в пути маршрута (см. site/local/routes/api.php) и/или
 * дополнительной проверке подписи, если ApiShip её предоставляет.
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
     * @return array<string,mixed>
     */
    public function handleAction(): array
    {
        $rawBody = (string)$this->getRequest()->getInput();
        $decoded = Json::decode($rawBody) ?: [];

        // ApiShip может присылать как один объект события, так и массив событий —
        // нормализуем к списку.
        $events = isset($decoded[0]) ? $decoded : [$decoded];

        $processed = 0;
        $duplicates = 0;

        foreach ($events as $eventData) {
            if (!is_array($eventData)) {
                continue;
            }

            $isNew = \service(WebhookServiceContract::class)->handle(
                WebhookEventDto::make($eventData),
                Json::encode($eventData)
            );

            $isNew ? $processed++ : $duplicates++;
        }

        return [
            'processed'  => $processed,
            'duplicates' => $duplicates,
        ];
    }
}
