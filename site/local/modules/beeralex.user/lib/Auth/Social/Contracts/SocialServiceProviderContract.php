<?php

namespace Beeralex\User\Auth\Social\Contracts;

/**
 * Контракт для провайдера социальных сервисов (обёртка над Bitrix SocialServices).
 *
 * Этот интерфейс описывает единый API для работы с внешними
 * социальными сервисами через адаптеры, не привязываясь
 * напрямую к реализациям Bitrix (CSocServAuth и т.д.).
 */
interface SocialServiceProviderContract
{
    /**
     * Уникальный идентификатор сервиса (например, 'GoogleOAuth', 'VKontakte', 'YandexOAuth').
     */
    public function getKey(): string;

    /**
     * Человекочитаемое название сервиса (например, 'Google', 'ВКонтакте').
     */
    public function getName(): string;

    /**
     * Возвращает URL для редиректа на страницу авторизации сервиса или html для вставки
     * Bitrix сам добавляет state, redirect_uri и т.д.
     *
     * @param array $params Дополнительные параметры (scope, redirect_uri и пр.)
     */
    public function getAuthorizationUrlOrHtml(array $params = []): array;

    /**
     * Проверяет, авторизован ли пользователь через данный сервис.
     * Обычно Bitrix хранит это в сессии после успешного OAuth.
     */
    public function isAuthorized(): bool;

    /**
     * Возвращает профиль текущего пользователя, полученный от соцсети.
     * Данные зависят от конкретного адаптера (Google, VK, Yandex и т.д.).
     *
     * @return array|null Например:
     * [
     *   'id' => '123456',
     *   'email' => 'user@example.com',
     *   'first_name' => 'Иван',
     *   'last_name' => 'Иванов'
     * ]
     */
    public function getProfile(): ?array;

    /**
     * Выполняет процесс авторизации через сервис (Bitrix сам создаёт и логинит пользователя).
     *
     * @return bool true, если Bitrix успешно авторизовал пользователя
     */
    public function authorize(): bool;
}
