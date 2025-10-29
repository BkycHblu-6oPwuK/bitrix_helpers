<? 
$this->addExternalJS("/local/templates/.default/scripts/vendor/v-mask.min.js");
?>
<div id="vue-payment-qr-code" style="min-height: 300px"></div>
<script type="text/x-template" id="vue-payment-qr-code-template">
    <div id="vue-payment-qr-code">
        <div v-if="!isPush">
            <div v-if="!isMobile && this.qr" class="qr-container">
            <img class="qr-image" :src="`data:image/png;base64,${this.qr}`">
            <div class="qr-container-title">
                <h1>Для оплаты</h1>
                <div v-if="!isSber">отсканируйте QR-код в мобильном приложении банка или штатной камерой телефона</div>
                <div v-else>отсканируйте QR-код в мобильном приложении СберБанка</div>
            </div>
            </div>
            <div v-else-if="!isSber && isMobile ">
            <div class="list">
                <p class="list__title">Выберите банковское приложение и подтвердите оплату</p>
                <div class="list__search">
                    <input type="text" v-model="searchTerm" @input="filterBanks" placeholder="Поиск по банкам">
                </div>
                <div class="list__inner-container">
                    <p class="list__title">Все банки</p>
                    <div class="list__inner" v-if="issetFilteredBanks">
                        <div v-for="(item, index) in filteredBanks" :key="item.schema" :title="item.bankName">
                            <a class="list__list-item list-item" :href="getBankLink(item.schema, item.package_name)">
                                <img width="50px" height="50px" class="list-item__logo" :src="item.logoURL" :alt="item.bankName" />
                                <span class="list-item__name">{{ item.bankName }}</span>
                            </a>
                        </div>
                    </div>
                    <div v-else-if="!isRequest">По вашему запросу ничего не найдено</div>
                </div>
            </div>
            </div>
            <div v-else-if="isSber && isMobile">
            <div class="list">
                <p class="list__title">Оплата через SberPay</p>
                <div class="list__inner-container">
                    <div class="list__inner">
                        <div>
                            <a class="button mod-first" :href="url">
                                <span class="list-item__name">Перейти в приложение</span>
                            </a>
                        </div>
                        <div v-if="showPush">
                            <button @click="showPushMethod" class="button mod-first"><span>Приложение не открывается</span></button> 
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <div v-if="showPush && isSber && !isMobile" style="max-width:400px;margin-top:20px;">
                <button @click="showPushMethod" class="button mod-first w-64"><span class="list-item__name">Оплатить через пуш</span></button> 
            </div>
        </div>
        <div v-else-if="showPush" style="max-width:400px;margin-top:20px;margin:0 auto;">
            <div @click="isPush = !isPush,pushSuccess = false">назад</div>
            <div v-if="!pushSuccess">
                <div>Введите номер телефона</div>
                <div class="form__row input">
                    <div class="input__container" :class="validationErrors.phone != null ? 'mod-error' : ''" :data-error="validationErrors.phone">
                        <input type="tel" v-mask="'+7 ### ###-##-##'" @input="changeTel" v-model="phone" @focus="validatePhone(true)" @blur="validatePhone" placeholder="Номер телефона получателя" :required="true" ref="phone">
                    </div>
                </div>
                <div><button :disabled="isRequest" @click="pushSberPay" class="button mod-first w-64"><span class="list-item__name">Отправить</span></button></div>
            </div>
            <div v-else>Уведомление отправлено</div>
        </div>
    </div>
</script>
<script>
    window.qrShowData = {
            qr: "<?= $arResult['QR'] ?>",
            url: "<?= $arResult['QR_URL'] ?>",
            showPush: "<?= $arParams['SHOW_PUSH'] ?>",
            isSber: false,
            orderId: "<?= $arResult['ORDER_ID'] ?>",
            check_payment: "<?= $arResult['CHECK_PAYMENT'] ?>",
            redirect_url: "<?= $arResult['REDIRECT_URL'] ?>",
            issetOrderId: "<?= $arResult['ISSET_ORDER_ID'] ?>",
            banks: [],
            filteredBanks: [],
            requestData: '',
            isRequest: true,
            searchTerm: "",
            isPush: false,
            phone: "+7",
            pushSuccess: false,
            isRequest: false,
            interval: null,
            iOS: (!window.MSStream && /iPad|iPhone|iPod/.test(navigator.userAgent)) ||
                /^((?!chrome|android).)*safari/i.test(navigator.userAgent),
            isMobile: (/Android|webOS|iPhone|iPad|iPod|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini/i
                .test(navigator.userAgent)),
            validationErrors: {
                phone: null,
            },
        }
</script>