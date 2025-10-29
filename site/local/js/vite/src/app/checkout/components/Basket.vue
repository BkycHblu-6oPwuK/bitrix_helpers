<script setup>
import { plural } from '@/common/js/helpers'
import { isExtaServiceSkipValue } from '@/store/checkout/helpers';
import { computed } from 'vue'
import { useStore } from 'vuex'

const store = useStore();
const total = computed(() => store.getters.getTotalPrice);
const deliveryItem = computed(() => store.getters.getSelectedDeliveryItem);
const delivery = computed(() => store.getters.getDelivery);
const extraServices = computed(() => store.getters.getExtraServices?.filter((element) => {
    if (element.values.length) {
        const serviceValue = element.value;
        if (!serviceValue) {
            return false;
        }
        for (let value of element.values) {
            if (value.id === serviceValue && isExtaServiceSkipValue(value)) {
                return false;
            }
        }
    }
    return true;
}));
const completionDate = computed(() => store.getters.getCompletionDate);
const payment = computed(() => store.getters.getPayments[store.getters.getActivePay]);
const selectedOwnDelivery = computed(() => store.getters.selectedOwnDelivery);
const saveOrder = () => store.dispatch('confirm');
</script>

<template>
    <div class="order-summary">
        <h3 class="order-summary__title">Ваш заказ:</h3>

        <div class="order-summary__row row__bottom-border">
            <div class="order-summary__label-group">
                <span class="order-summary__label summary-label__border">{{ total.totalQuantity + ' ' +
                    plural(total.totalQuantity,
                        ['товар', 'товара', 'товаров']) }}</span>
                <span class="order-summary__subtext">{{ total.totalWeight }} кг</span>
            </div>
            <div class="order-summary__value summary-value__bold">{{ total.totalItemsPrice }} руб</div>
        </div>

        <div class="order-summary__row" v-if="selectedOwnDelivery && !deliveryItem.price">
            <div class="order-summary__label order-summary__label-warning">
                Стоимость доставки и разгрузки уточнит оператор
            </div>
        </div>
        <div class="order-summary__row" v-else>
            <div class="order-summary__label">
                {{ deliveryItem.name }}:
                <div class="order-summary__subtext">{{ delivery.address }}</div>
            </div>
            <div class="order-summary__value summary-value__bold" v-if="deliveryItem.price">{{ deliveryItem.price }} руб</div>
        </div>

        <template v-if="extraServices">
            <template v-for="service in extraServices" :key="service.id">
                <div class="order-summary__row">
                    <span class="order-summary__label">{{ service.title }}</span>
                    <span class="order-summary__value summary-value__bold">{{ service.price ? service.price + ' руб' :
                        'договорная' }}</span>
                </div>
            </template>
        </template>

        <div class="order-summary__row">
            <span class="order-summary__label">Дата:</span>
            <span class="order-summary__value summary-value__bold">{{ completionDate }}</span>
        </div>

        <div class="order-summary__row row__bottom-border">
            <span class="order-summary__label">Оплата:</span>
            <span class="order-summary__value">{{ payment.name }}</span>
        </div>

        <div class="order-summary__total">
            <span class="order-summary__label">ИТОГО:</span>
            <span class="order-summary__value summary-value__bold">{{ total.totalPrice }} руб</span>
        </div>

        <div class="order-summary__actions">
            <p class="order-summary__notice">
                Нажимая на кнопку, даю согласие на обработку персональных данных
            </p>

            <button class="order-summary__submit" @click="saveOrder">Подтвердить заказ</button>
            <a class="order-summary__back" href="/catalog/" type="button">Вернуться к покупкам</a>
        </div>
    </div>
</template>

<style scoped>
.order-summary {
    margin: 0 auto;
    padding: 30px 15px;
    border: 1px solid #E5E5E5;
    border-radius: 3px;
    height: fit-content;
    position: sticky;
    top: 115px;
    width: 100%;
    font-size: 16px;
    line-height: 24px;
    background: #FBFBFB;
}

.order-summary__title {
    font-size: 24px;
    line-height: 32px;
    font-weight: 700;
    margin-bottom: 25px;
}

.order-summary__row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 10px;
}

.order-summary__row.row__bottom-border {
    border-bottom: 1px solid #8D9091;
    padding-bottom: 15px;
    margin-bottom: 15px;
}

.order-summary__label {
    font-weight: 700;
}

.order-summary__label.order-summary__label-warning {
    color: #EE8C00;
}

.order-summary__label.summary-label__border {
    text-decoration: underline;
}

.order-summary__total .order-summary__label {
    font-size: 18px;
    line-height: 32px;
}

.order-summary__label-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.order-summary__subtext {
    font-size: 14px;
    color: #8D9091;
    line-height: 17px;
    font-weight: 400;
}

.order-summary__value {
    white-space: nowrap;
}

.order-summary__value.summary-value__bold {
    font-weight: 700;
}

.order-summary__total {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: bold;
    margin-bottom: 15px;
    font-size: 16px;
}

.order-summary__notice {
    font-size: 12px;
    color: #999;
    max-width: 250px;
    line-height: 16px;
    text-align: center;
}

.order-summary__actions {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.order-summary__submit {
    display: block;
    width: 100%;
    max-width: 205px;
    background: #ff7a00;
    color: #fff;
    border: none;
    padding: 10px;
    border-radius: 4px;
    font-weight: 400;
    font-size: 14px;
    line-height: 19px;
    cursor: pointer;
}

.order-summary__back {
    display: block;
    width: 100%;
    text-align: center;
    max-width: 205px;
    background: #fff;
    color: #000;
    border: 1px solid #ccc;
    padding: 10px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 400;
    line-height: 17px;
    font-size: 14px;
}

@media (min-width: 768px) {
    .order-summary {
        padding: 30px;
    }
}
</style>
