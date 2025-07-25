<script setup>
import Basket from './components/Basket.vue';
import Deliveryes from './components/Deliveryes.vue';
import Form from './components/Form.vue';
import Payments from './components/Payments.vue';
import PersonTypes from './components/PersonTypes.vue';
import TakeDate from './components/TakeDate.vue';

</script>

<template>
    <div class="checkout-block">
        <div class="checkout-info">
            <PersonTypes></PersonTypes>
            <Form></Form>
            <Deliveryes></Deliveryes>
            <TakeDate></TakeDate>
            <Payments></Payments>
        </div>
        <Basket></Basket>
    </div>
</template>

<style scoped>
.checkout-block {
    margin-bottom: 30px;
}

.checkout-info {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

@media (min-width: 768px) {
    .checkout-info {
        gap: 50px;
    }

    .checkout-block {
        margin-bottom: 50px;
    }
}

@media (min-width: 992px) {
    .checkout-block {
        display: grid;
        grid-template-columns: 1fr 450px;
        gap: 30px;
    }
}
</style>