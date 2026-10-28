import type { CatalogItemDTO, OffersTreePropsValuesItemDTO } from '~/types/iblock/catalog'

type OfferValues = Record<string, string>

export interface OfferOption extends OffersTreePropsValuesItemDTO {
    /** Это значение выбрано сейчас */
    selected: boolean
    /** Есть предложение в наличии с этим значением при текущем выборе остальных свойств */
    available: boolean
    /** Подпись для вывода (с единицей измерения и т.п.). `value` остаётся ключом для выбора */
    label: string
}

export interface OfferOptionGroup {
    code: string
    name: string
    options: OfferOption[]
}

interface Options {
    /** ID предложения, которое нужно выбрать при инициализации (например, из ?offer=) */
    initialOfferId?: MaybeRefOrGetter<number | null | undefined>
    /** Как показывать значение свойства в интерфейсе. По умолчанию — как есть */
    formatValue?: (propCode: string, value: string) => string
}

const matches = (values: OfferValues, selected: OfferValues) =>
    Object.entries(selected).every(([code, value]) => values[code] === value)

/**
 * Выбор торгового предложения по свойствам (литраж, цвет и т.д.).
 *
 * Источник правды — только `selectedValues`. `selectedOffer` и список вариантов
 * для UI вычисляются из него, поэтому рассинхрона между ними быть не может.
 * Инициализация происходит синхронно в setup (работает с SSR, без hydration mismatch)
 * и повторяется при смене товара.
 */
export function useCatalogOffersTree(
    item: MaybeRefOrGetter<CatalogItemDTO | null | undefined>,
    options: Options = {},
) {
    const selectedValues = ref<OfferValues>({})

    const props = computed(() => toValue(item)?.offersTree?.props ?? [])

    // Bitrix/PHP отдаёт пустую карту как [] вместо {}
    const entries = computed(() => {
        const map = toValue(item)?.offersTree?.map
        if (!map || Array.isArray(map)) return []
        return Object.entries(map).map(([id, values]) => ({ id: Number(id), values: values as OfferValues }))
    })

    const offersById = computed(() => new Map((toValue(item)?.offers ?? []).map(o => [o.id, o])))

    const isAvailable = (offerId: number) => {
        const offer = offersById.value.get(offerId)
        return !!offer && (offer.catalog?.available ?? true)
    }

    const selectedOffer = computed(() => {
        // Простой товар без свойств в дереве
        if (!props.value.length) return toValue(item)?.preselectedOffer ?? null

        const entry = entries.value.find(e => matches(e.values, selectedValues.value))
        return entry ? offersById.value.get(entry.id) ?? null : null
    })

    /** Варианты для каждой «оси» (литраж, цвет…) уже с флагами selected/available */
    const optionGroups = computed<OfferOptionGroup[]>(() =>
        props.value.map((prop) => {
            const others = Object.entries(selectedValues.value).filter(([code]) => code !== prop.code)

            return {
                code: prop.code,
                name: prop.name,
                options: prop.values.map(value => ({
                    ...value,
                    label: options.formatValue?.(prop.code, value.value) ?? value.value,
                    selected: selectedValues.value[prop.code] === value.value,
                    available: entries.value.some(e =>
                        e.values[prop.code] === value.value
                        && others.every(([code, v]) => e.values[code] === v)
                        && isAvailable(e.id),
                    ),
                })),
            }
        }),
    )

    const pickInitialValues = (): OfferValues => {
        const wantedId = toValue(options.initialOfferId) ?? toValue(item)?.preselectedOffer?.id

        const entry = entries.value.find(e => e.id === wantedId)
            ?? entries.value.find(e => isAvailable(e.id))
            ?? entries.value[0]

        if (entry) return { ...entry.values }

        // Карты нет — берём первое значение каждого свойства
        return Object.fromEntries(
            props.value.flatMap(p => (p.values[0] ? [[p.code, p.values[0].value]] : [])),
        )
    }

    // immediate: выбор готов уже при первом рендере (SSR) и пересчитывается при смене товара
    watch(() => toValue(item)?.id, () => {
        selectedValues.value = pickInitialValues()
    }, { immediate: true })

    /**
     * Выбор значения свойства.
     * Если с остальными выбранными значениями такой комбинации нет,
     * подбирается ближайшее предложение (больше совпадений, предпочтительно в наличии).
     */
    const selectOfferValue = (propCode: string, value: string) => {
        if (selectedValues.value[propCode] === value) return

        const next = { ...selectedValues.value, [propCode]: value }
        if (entries.value.some(e => matches(e.values, next))) {
            selectedValues.value = next
            return
        }

        let best: { values: OfferValues; score: number } | null = null
        for (const entry of entries.value) {
            if (entry.values[propCode] !== value) continue

            const matched = Object.entries(selectedValues.value)
                .filter(([code, v]) => code !== propCode && entry.values[code] === v).length
            const score = matched * 2 + (isAvailable(entry.id) ? 1 : 0)

            if (!best || score > best.score) best = { values: entry.values, score }
        }

        if (best) selectedValues.value = { ...best.values }
    }

    return { selectedValues, selectedOffer, optionGroups, selectOfferValue }
}