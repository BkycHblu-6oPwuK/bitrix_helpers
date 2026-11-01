<script setup lang="ts">
const { challengeUrl, setToken, invalidate, setStart, fail } = useAltcha()
const widgetRef = useTemplateRef<any>('widgetRef')

onMounted(() => {
    const widget = widgetRef.value
    if (!widget) return

    setStart(async () => {
        try {
            await import('altcha')
            await customElements.whenDefined('altcha-widget')
            widget.reset()
            widget.verify()
        } catch {
            fail()
        }
    })

    widget.addEventListener('verified', (e: Event) => {
        const payload: string | undefined = (e as CustomEvent).detail?.payload
        if (!payload) return

        let expiresAt = Date.now() + 5 * 60 * 1000
        try {
            const raw = JSON.parse(atob(payload))?.challenge?.parameters?.expiresAt
            if (typeof raw === 'number' && raw > 0) expiresAt = raw * 1000
        } catch { }

        setToken(payload, expiresAt)
    })

    widget.addEventListener('statechange', (e: Event) => {
        const state = (e as CustomEvent).detail?.state
        if (state === 'expired') {
            invalidate()
            widget.reset()
        } else if (state === 'error') {
            fail()
        }
    })
})

onBeforeUnmount(() => setStart(null))
</script>

<template>
    <altcha-widget ref="widgetRef" display="invisible" :challenge="challengeUrl" style="display:none" />
</template>