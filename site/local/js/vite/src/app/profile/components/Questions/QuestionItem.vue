<script setup>
const props = defineProps({
    item: Object
})
</script>

<template>
    <div class="profile-qa__item">
        <div class="profile-qa__item-question"><span>{{ props.item.question }}</span></div>
        <div class="profile-qa__item-answer">
            <span>{{ props.item.answer }}</span>
        </div>
    </div>
</template>