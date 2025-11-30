/**
 * Типы для элементов инфоблоков Bitrix
 * Базовый тип для всех элементов (статьи, новости, товары и т.д.)
 */

import type { PropertyItemDTO } from "./property"

/**
 * Базовый элемент инфоблока
 * Содержит стандартные поля Bitrix и пользовательские свойства
 */
export interface ElementDTO {
  id: number                     // Уникальный ID элемента
  code: string                   // Символьный код
  name: string                   // Название элемента

  previewText: string            // Анонс (краткое описание)
  previewPicture: string         // URL картинки анонса
  detailText: string             // Подробное описание
  detailPicture: string          // URL детальной картинки

  detailPageUrl: string          // URL детальной страницы
  listPageUrl: string            // URL списка/раздела
  dateCreate: string             // Дата создания (ISO формат)

  properties: PropertyItemDTO[]  // Массив пользовательских свойств
}
