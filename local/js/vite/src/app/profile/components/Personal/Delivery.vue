<script setup>

</script>

<template>
    <div class="profile-personal__address">
        <h3 class="profile__subtitle">Адрес для доставки</h3>
        <div>
            <div class="profile-personal__address-inputs">
                <div class="profile-personal__address-street">
                    <label for="checkout-street">Адрес для доставки</label>
                    <input id="checkout-street" type="text" placeholder="Улица, Дом, Подъезд">
                </div>
                <div class="profile-personal__address-entrance">
                    <label for="checkout-entrance">Подъезд</label>
                    <input id="checkout-entrance" type="text" placeholder="5">
                </div>
                <div class="profile-personal__address-house">
                    <label for="checkout-house">Квартира</label>
                    <input id="checkout-house" type="text" placeholder="54">
                </div>
            </div>
            <div id="map" class="yandex-maps"></div>
        </div>
    </div>
</template>