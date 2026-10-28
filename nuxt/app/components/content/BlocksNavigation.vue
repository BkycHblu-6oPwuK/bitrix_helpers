<script setup lang="ts">

const { selector = '.content-blocks' } = defineProps<{
    selector?: string
}>()

const items = ref<{
    id: string
    title: string
}[]>([])

function getItemTitle(item: Element) {
    return Array.from(item.childNodes)
        .filter(node => node.nodeType === Node.TEXT_NODE)
        .map(node => node.textContent?.trim())
        .filter(Boolean)
        .join(' ')
}

onMounted(() => {
    const root = document.querySelector(selector)

    if (!root) {
        return
    }

    const listItems = root.querySelectorAll(
        ':scope > * > ol > li, :scope > * > ul > li'
    )
    let index = 0;
    items.value = Array.from(listItems).map((item) => {
        const id = `content-section-${index + 1}`
        index++;

        item.id = id
        return {
            id,
            title: getItemTitle(item),
        }
    })
})
</script>

<template>
    <nav
        class="content-blocks-navigation"
        aria-label="Навигация по странице"
    >
        <ul v-if="items.length">
            <li
                v-for="(item) in items"
                :key="item.id"
            >
                <a :href="`#${item.id}`">
                    {{ item.title }}
                </a>
            </li>
        </ul>
    </nav>
</template>