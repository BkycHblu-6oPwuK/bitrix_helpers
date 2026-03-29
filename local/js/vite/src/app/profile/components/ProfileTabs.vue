<script setup>

</script>

<template>
    <div class="profile__info-tabs">
        <RouterLink :to="{name: 'account'}" class="profile__info-tab profile__info-tab-personal"><span>Персональные данные</span></RouterLink>
        <RouterLink :to="{name: 'orders'}" class="profile__info-tab profile__info-tab-orders"><span>Мои заказы</span></RouterLink>
        <RouterLink :to="{name: 'dressings'}" class="profile__info-tab profile__info-tab-orders"><span>Мои примерки</span></RouterLink>
        <RouterLink :to="{name: 'questions'}" class="profile__info-tab profile__info-tab-qa"><span>Вопросы и ответы</span></RouterLink>
    </div>
</template>