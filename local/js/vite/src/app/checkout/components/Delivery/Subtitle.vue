<template>
    <h4 class="checkout-transport__subtitle">
        <slot />
    </h4>
</template>

<style scoped>
.checkout-transport__subtitle {
    color: #000000;
    font-weight: 700;
    font-size: 14px;
    line-height: 18px;
    margin-bottom: 15px;
}

@media (min-width: 768px) {
    .checkout-transport__subtitle {
        font-size: 18px;
        line-height: 32px;
    }
}
</style>