import { send } from '@/api/order';
import { closePreloader, showPreloader } from '@/app/preloader';
import { createStore } from 'vuex';
import { getOrderParams, isOwnDelivery, isShopDelivery, isTransportDelivery, processErrorsComponent, validateCheckout } from './helpers';
import { showErrorNotification } from '@/app/notify';
import ResultError from '@/lib/ResultError';

const store = createStore({
    state: {
        items: {},
        delivery: {},
        payments: {},
        activePay: '',
        totalPrice: {},
        coupon: {},
        personType: {},
        form: {},
        rules: {},
        propIdsMap: {},
        profileId: '',
        comment: '',
        siteId: '',
        signedParams: '',
        errors: {}
    },
    mutations: {
        setItems(state, items) {
            state.items = items;
        },
        setDelivery(state, delivery) {
            state.delivery = delivery;
        },
        setPayments(state, methods) {
            state.payments = methods;
        },
        setActivePay(state, activePay) {
            state.activePay = activePay;
        },
        setTotalPrice(state, totalPrice) {
            state.totalPrice = totalPrice;
        },
        setCoupon(state, coupon) {
            state.coupon = coupon;
        },
        setPersonType(state, personType) {
            state.personType = personType;
        },
        setForm(state, form) {
            state.form = form;
        },
        setRules(state, rules) {
            state.rules = rules;
        },
        setPropIdsMap(state, propIdsMap) {
            state.propIdsMap = propIdsMap;
        },
        setProfileId(state, profileId) {
            state.profileId = profileId;
        },
        setComment(state, comment) {
            state.comment = comment;
        },
        setSiteId(state, siteId) {
            state.siteId = siteId;
        },
        setSignedParams(state, signedParams) {
            state.signedParams = signedParams;
        },
        setCity(state, city) {
            state.delivery.city = city;
        },
        setPostCode(state, postCode) {
            state.delivery.postCode = postCode;
        },
        setLocation(state, location) {
            state.delivery.location = location;
        },
        setAddress(state, address) {
            state.delivery.address = address;
        },
        setCoordinates(state, coordinates) {
            state.delivery.coordinates = coordinates;
        },
        setDeliverySelectedId(state, selectedId) {
            state.delivery.selectedId = selectedId;
        },
        setDeliveryPvzId(state, pvzId) {
            state.delivery.selectedPvz = pvzId;
        },
        setDeliveryCompletionDate(state, completionDate) {
            state.delivery.completionDate = completionDate;
        },
        setSelectedStoreId(state, id) {
            state.delivery.storeSelectedId = id;
        },
        setErrors(state, errors) {
            state.errors = errors;
        },
        setSelectedPersonType(state, code) {
            state.personType.selected = code
        },
        setExtraServiceValue(state, { serviceId, value }) {
            const selectedDeliveryId = state.delivery.selectedId;
            const deliveries = state.delivery.deliveries;

            if (!deliveries || !deliveries[selectedDeliveryId]) return;

            const extraServices = deliveries[selectedDeliveryId].extraServices;

            if (!Array.isArray(extraServices)) return;
            const updatedServices = extraServices.map(service => {
                if (service.id === serviceId) {
                    return { ...service, value };
                }
                return service;
            });

            deliveries[selectedDeliveryId].extraServices = updatedServices;
        }

    },
    actions: {
        initialize({ commit, state, dispatch }, data) {
            dispatch('setCheckoutDTO', data.checkoutDTO);
            commit('setSiteId', data.siteId);
            commit('setSignedParams', data.signedParams);
            console.log(state)
        },
        setCheckoutDTO({ commit }, checkoutDTO) {
            commit('setItems', checkoutDTO.items);
            commit('setDelivery', checkoutDTO.delivery);
            commit('setPayments', checkoutDTO.payments);
            commit('setActivePay', checkoutDTO.activePay);
            commit('setTotalPrice', checkoutDTO.totalPrice);
            commit('setCoupon', checkoutDTO.coupon);
            commit('setPersonType', checkoutDTO.personType);
            commit('setForm', checkoutDTO.form);
            commit('setRules', checkoutDTO.rules);
            commit('setPropIdsMap', checkoutDTO.propIdsMap);
            commit('setProfileId', checkoutDTO.profileId);
            commit('setComment', checkoutDTO.comment);
        },
        async refresh({ getters, dispatch }, isShowPreloader = true) {
            try {
                if (isShowPreloader) {
                    showPreloader();
                }
                const formData = getOrderParams(getters)
                const result = await send('refreshOrderAjax', formData);
                dispatch('setCheckoutDTO', result.checkoutDTO);
            } catch (error) {
                if (error instanceof ResultError) {
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при обновлении заказа', error);
            } finally {
                closePreloader();
            }
        },
        async confirm({ getters, dispatch }) {
            dispatch('validate');
            if (!getters.errorsIsEmpty) {
                return;
            }
            try {
                showPreloader();
                const formData = getOrderParams(getters)
                const result = await send('saveOrderAjax', formData);
                if (result.ERROR) {
                    processErrorsComponent(result.ERROR)
                } else if (result.REDIRECT_URL) {
                    window.location.href = result.REDIRECT_URL
                }
            } catch (error) {
                if (error instanceof ResultError) {
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при сохранении заказа', error);
            } finally {
                closePreloader();
            }
        },
        validate({ getters, commit }) {
            commit('setErrors', {});
            const errors = validateCheckout(getters);
            commit('setErrors', errors);
        },
        resetLocation({ commit }) {
            commit('setDeliveryPvzId', "");
            commit('setAddress', '');
            commit('setCoordinates', '');
        },
    },
    getters: {
        getItems: (state) => state.items,
        getDelivery: (state) => state.delivery,
        getSelectedDeliveryItem: (state) => state.delivery.deliveries[state.delivery.selectedId] || null,
        selectedTransportDelivery: (state) => isTransportDelivery(state.delivery.deliveries[state.delivery.selectedId]),
        selectedOwnDelivery: (state) => isOwnDelivery(state.delivery.deliveries[state.delivery.selectedId]),
        selectedShopDelivery: (state) => isShopDelivery(state.delivery.deliveries[state.delivery.selectedId]),
        getPriceExtraService: (state) => {
            const delivery = state.delivery.deliveries[state.delivery.selectedId];
            if (!delivery || !delivery.extraServices.length) return null;
            return delivery.extraServices.find((element) => element.code === 'DISTANCE_PRICE_SERVICE') || null
        },
        getExtraServices: (state) => {
            const delivery = state.delivery.deliveries[state.delivery.selectedId];
            if (!delivery || !delivery.extraServices.length) return null;
            return delivery.extraServices.filter((element) => element.code !== 'DISTANCE_PRICE_SERVICE') || null
        },
        getPayments: (state) => state.payments,
        getActivePay: (state) => state.activePay,
        getActivePayId: (state) => state.propIdsMap.payments[state.activePay] || 0,
        getDisplayAddress: (state) => {
            const delivery = state.delivery;
            if (delivery.selectedPvz && delivery.address) {
                return delivery.city + ', ' + delivery.address;
            }
            return delivery.city;
        },
        getCompletionDate: (state) => state.delivery.completionDate || '',
        getTotalPrice: (state) => state.totalPrice,
        getCoupon: (state) => state.coupon,
        getPersonType: (state) => state.personType,
        isLegal: (state) => state.personType.selected === 'legal',
        getForm: (state) => state.form,
        getRules: (state) => state.rules,
        getPropIdsMap: (state) => state.propIdsMap,
        getProfileId: (state) => state.profileId,
        getComment: (state) => state.comment,
        getSiteId: (state) => state.siteId,
        getSignedParams: (state) => state.signedParams,
        getErrors: (state) => state.errors,
        errorsIsEmpty: (state) => Object.values(state.errors).length === 0,
    },
});

export default store;