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
    <div class="border border-dashed border-gray-400 bg-gray-50 p-4 my-4 rounded-xl text-sm text-gray-800">
        <template v-if="label">
            <code class="text-blue-600">{{ label }}</code>
        </template>

        <div v-if="data !== undefined && data !== null" class="mt-2">
            <details>
                <summary class="cursor-pointer text-gray-600 hover:text-black">Показать данные</summary>
                <pre class="text-xs mt-2 bg-white p-2 rounded overflow-auto">{{ formattedData }}</pre>
            </details>
        </div>
        <div v-else class="mt-2 text-gray-500 italic">Нет данных</div>
    </div>
</template>
