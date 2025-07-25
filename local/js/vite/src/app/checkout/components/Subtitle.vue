<template>
    <h3 class="checkout-info__subtitle">
        <slot />
    </h3>
</template>

<style scoped>
.checkout-info__subtitle {
    color: #212121;
    font-weight: 700;
    margin-bottom: 15px;
    font-size: 18px;
    line-height: 23px;
}

@media (min-width: 768px) {
    .checkout-info__subtitle {
        font-size: 24px;
        line-height: 32px;
        margin-bottom: 30px;
    }
}
</style>