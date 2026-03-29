import { useSectionStore } from '~/stores/section'
import { storeToRefs } from 'pinia'
import { computed } from 'vue'

/**
 * Композабл для работы с фильтрацией секции
 * Предоставляет доступ к состоянию фильтров, выбранным значениям и методам управления
 * 
 * @returns Объект с данными фильтров и методами для работы с ними
 */
export function useSectionFilter() {
  const store = useSectionStore()
  
  // Получаем реактивные данные из store
  const { selectedFilters, currentSortId, filterData } = storeToRefs(store)
  const { hasActiveFilters, selectedFilterCount } = storeToRefs(store)

  // URL для применения текущих фильтров (из данных бэкенда)
  const filterUrl = computed(() => {
    if (!filterData.value) return null
    return filterData.value.filterUrl
  })

  // URL для сброса всех фильтров (из данных бэкенда)
  const clearUrl = computed(() => {
    if (!filterData.value) return null
    return filterData.value.clearUrl
  })

  return {
    // State - текущее состояние фильтров
    filterData, // Данные всех доступных фильтров
    selectedFilters, // Выбранные значения фильтров {controlId: value}
    currentSortId, // ID текущей сортировки
    
    // Getters - вычисляемые свойства
    filterUrl, // URL для применения фильтров
    clearUrl, // URL для сброса фильтров
    hasActiveFilters, // Есть ли активные фильтры
    selectedFilterCount, // Количество выбранных фильтров
    
    // Actions - методы для управления фильтрами
    toggleFilter: store.toggleFilter, // Включить/выключить фильтр
    clearFilters: store.clearFilters, // Очистить все фильтры
    setSorting: store.setSorting, // Установить сортировку
    buildFilterUrl: store.buildFilterUrl, // Построить URL с фильтрами
  }
}
