document.addEventListener('DOMContentLoaded', function() {
    Vue.directive('mask', VueMask.VueMaskDirective);
    qrSection = new Vue({
        el: "#vue-payment-qr-code",
        template: '#vue-payment-qr-code-template',
        data: window.qrShowData,
        async created() {
            this.isSber = this.url.startsWith('sberpay://') || this.url.startsWith('sbolpay://');
            if (this.isMobile && !this.isSber) {
                this.getBanks();
            }
            if (this.isMobile && this.isSber) {
                if (this.iOS) {
                    var index = this.url.indexOf('://invoicing');
                    if (index !== -1) {
                        var baseUrl = this.url.substring(index);
                        var sbolpayUrl = 'sbolpay' + baseUrl;
                        this.url = sbolpayUrl;
                    }
                }
            }
            if (this.check_payment) {
                this.requestData = encodeURIComponent(`orderId='${this.orderId}'`);
                setTimeout(() => {
                    this.setIntervalMethod()
                }, 10000);
            }
        },
        mounted() {
            if(this.isSber && this.isMobile){
                this.openSberPay()
            }
        },
        methods: {
            changeTel(e) {
                let isFullLength = this.form.phone.replace(/\D/g, '').length === 12;
                if (isFullLength && (this.form.phone.startsWith('+7 8') || this.form.phone.startsWith('+7 7'))) {
                    let newRaw = this.form.phone.replace(/^\+7 [87]/, '+7 ');
                    newRaw += e.target.value?.replace(/\D/g, '') || '';
                    this.form.phone = newRaw;
                }
            },
            showPushMethod(){
                this.isPush = !this.isPush
                clearInterval(this.interval);
                setTimeout(() => {
                    this.setIntervalMethod()
                }, 10000);
            },
            async getBanks() {
                try {
                    const response = await fetch("https://qr.nspk.ru/proxyapp/c2bmembers.json");

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const data = await response.json();
                    this.banks = data.dictionary;
                    this.filteredBanks = data.dictionary
                } catch (error) {
                    console.error('Error:', error);
                } finally {
                    this.isRequest = false
                }
            },
            async setIntervalMethod(){
                this.interval = setInterval(async () => {
                    await this.checkPayment();
                    if (new Date() - startTime >= 5 * 60 * 1000) {
                        clearInterval(this.interval);
                    }
                }, 3000);
                const startTime = new Date();
            },
            openSberPay() {
                window.location.href = this.url;
            },
            async checkPayment() {
                try {
                    const response = await fetch('/bitrix/services/main/ajax.php?mode=class&c=Itb:qrShow&action=checkPayment', {
                        method: "POST",
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                        },
                        body: 'requestData=' + this.requestData,
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const data = await response.json();
                    if (data.data.paid === true) {
                        let redirectUrl;
                        if (this.redirect_url.length > 0) {
                            if (this.issetOrderId) {
                                redirectUrl = `${this.redirect_url}${data.data.order_id}`
                            } else {
                                redirectUrl = this.redirect_url
                            }
                            window.location.href = redirectUrl;
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            },
            getBankLink(schema, package_name) {
                url = this.iOS ?
                    `${this.url.replace("https:", schema + ":")}` :
                    `${this.url.replace(
                      "https:",
                      "intent:"
                    )}#Intent;scheme=${schema};package=${package_name};end;`;
                return url
            },
            filterBanks() {
                const normalizedSearchTerm = this.searchTerm.toLowerCase().trim();

                if (!normalizedSearchTerm) {
                    this.filteredBanks = this.banks;
                    return;
                }

                this.filteredBanks = this.banks.filter(bank =>
                    bank.bankName.toLowerCase().includes(normalizedSearchTerm)
                );
            },
            async pushSberPay() {
                if (this.validatePhone()) {
                    try {
                        let formData = new URLSearchParams();
                        formData.append('orderId', this.orderId);
                        formData.append('sendSms', 1);
                        formData.append('sessid', BX.bitrix_sessid());
                        this.isRequest = true
                        const response = await fetch("/bitrix/services/main/ajax.php?mode=ajax&c=oneway:order&action=initPay", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: formData
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }

                        const data = await response.json();
                    } catch (error) {
                        console.error('Error:', error);
                    } finally {
                        this.pushSuccess = true
                        this.isRequest = false
                    }
                }
            },
            validatePhone(noCheck = false) {
                let isValid = true;
                if (noCheck == true) {
                    this.$set(this.validationErrors, 'phone', null);
                    return isValid;
                }
                if (!this.phone) {
                    this.$set(this.validationErrors, 'phone', 'Введите номер телефона');
                    isValid = false;
                } else if (!/^\+7 \d{3} \d{3}-\d{2}-\d{2}$/.test(this.phone)) {
                    this.$set(this.validationErrors, 'phone', 'Введите номер телефона');
                    isValid = false;
                } else {
                    this.$set(this.validationErrors, 'phone', null);
                }
                return isValid;
            },
        },
        computed: {
            issetFilteredBanks() {
                return !this.isRequest && this.filteredBanks.length > 0
            }
        }
    });
});