<script setup lang="ts">
import type { AuthMethod } from '~/types/auth';

const props = defineProps<{
    methods: AuthMethod[]
}>()

const socialConfig: Record<string, { icon: string; label: string; color?: string }> = {
    telegram: {
        icon: 'i-simple-icons-telegram',
        label: 'Telegram',
        color: 'blue'
    },
    yandexoauth: {
        icon: 'i-simple-icons-yandex',
        label: 'Yandex',
        color: 'red'
    },
}

const normalizeType = (type: string): string => {
    return type.toLowerCase().replace(/oauth$/, 'oauth')
}

const getConfig = (method: AuthMethod) => {
    const normalized = normalizeType(method.type)
    return socialConfig[normalized] || {
        icon: 'i-heroicons-globe-alt',
        label: method.type,
    }
}

const handleSocialAuth = (method: AuthMethod) => {
    if (method.authType === 'url' && method.value) {
        const width = 600
        const height = 700
        const left = (window.screen.width - width) / 2
        const top = (window.screen.height - height) / 2

        window.open(
            method.value,
            'oauth',
            `width=${width},height=${height},left=${left},top=${top}`
        )
    } else if (method.authType === 'html' && method.value) {
        injectHtmlWidget(method)
    }
}

const injectHtmlWidget = (method: AuthMethod) => {
    const containerId = `social-widget-${normalizeType(method.type)}`
    const container = document.getElementById(containerId)

    if (container && method.value) {
        container.innerHTML = method.value

        const scripts = container.getElementsByTagName('script')
        for (let i = 0; i < scripts.length; i++) {
            const script = scripts[i]
            const newScript = document.createElement('script')

            Array.from(script.attributes).forEach(attr => {
                newScript.setAttribute(attr.name, attr.value)
            })

            if (script.innerHTML) {
                newScript.innerHTML = script.innerHTML
            }

            script.parentNode?.replaceChild(newScript, script)
        }
    }
}

const hasHtmlWidgets = computed(() =>
    props.methods.some(m => m.authType === 'html')
)
</script>

<template>
    <div v-if="methods.length > 0" class="mt-6">
        <div class="relative mb-4">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-700"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-gray-900 text-gray-400">Или войти через</span>
            </div>
        </div>

        <div class="space-y-3">
            <template v-for="method in methods" :key="method.type">
                <UButton v-if="method.authType === 'url'" block variant="outline" size="lg"
                    :icon="getConfig(method).icon" @click="handleSocialAuth(method)">
                    {{ getConfig(method).label }}
                </UButton>

                <div v-if="method.authType === 'html'" :id="`social-widget-${normalizeType(method.type)}`"
                    class="flex justify-center items-center min-h-[48px]">
                    <UButton block variant="outline" size="lg" :icon="getConfig(method).icon"
                        @click="handleSocialAuth(method)">
                        {{ getConfig(method).label }}
                    </UButton>
                </div>
            </template>
        </div>

        <p v-if="hasHtmlWidgets" class="text-xs text-gray-500 mt-3 text-center">
            Нажмите на кнопку для загрузки виджета авторизации
        </p>
    </div>
</template>
