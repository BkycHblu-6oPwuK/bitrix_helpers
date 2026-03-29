<script setup lang="ts">
import { computed } from 'vue'

type Dumpable =
    | Record<string, unknown>
    | unknown[]
    | string
    | number
    | boolean
    | null
    | undefined

interface Props {
    data?: Dumpable
    label?: string
}

const props = defineProps<Props>()

const formattedData = computed(() => {
    const { data } = props
    if (data === undefined || data === null) return 'null'

    try {
        return JSON.stringify(data, null, 2)
    } catch {
        return String(data)
    }
})
</script>

<template>
    <div
        class="border border-dashed border-gray-400 bg-gray-50 dark:border-gray-600 dark:bg-gray-800 p-4 my-4 rounded-xl text-sm text-gray-800 dark:text-gray-200 transition-colors duration-300">
        <template v-if="label">
            <code class="text-blue-600 dark:text-blue-400">{{ label }}</code>
        </template>

        <div v-if="data !== undefined && data !== null" class="mt-2">
            <details>
                <summary
                    class="cursor-pointer text-gray-600 hover:text-black dark:text-gray-400 dark:hover:text-white transition-colors">
                    Показать данные
                </summary>
                <pre
                    class="text-xs mt-2 bg-white dark:bg-gray-900 p-2 rounded overflow-auto text-gray-800 dark:text-gray-100 transition-colors duration-300">
            {{ formattedData }}
        </pre>
            </details>
        </div>

        <div v-else class="mt-2 text-gray-500 dark:text-gray-400 italic">
            Нет данных
        </div>
    </div>
</template>
