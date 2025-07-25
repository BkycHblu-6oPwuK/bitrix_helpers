import { createStore } from 'vuex';
import { addProductVieweded, addReview, changeColor } from '@/api/catalog';
import { showPreloader, closePreloader } from '@/app/preloader';
import ResultError from '@/lib/ResultError';
import { showErrorNotification } from '@/app/notify';

const store = createStore({
    state: {
        product: {},
        reviews: {},
        propertiesDefault: {},
        properties: {},
        colors: [],
        actions: {},
        delivery: {},
        siteId: '',
        signedParameters: '',
    },
    mutations: {
        setProduct(state, product) {
            state.product = product;
        },
        setPropertiesDefault(state, properties) {
            state.propertiesDefault = properties;
        },
        setProperties(state, properties) {
            state.properties = properties;
        },
        setColors(state, colors) {
            state.colors = colors;
        },
        setActions(state, actions) {
            state.actions = actions;
        },
        setDelivery(state, delivery) {
            state.delivery = delivery
        },
        setSiteId(state, siteId) {
            state.siteId = siteId;
        },
        setSignedParameters(state, signedParameters) {
            state.signedParameters = signedParameters;
        },
        setPreselectedOffer(state, offer) {
            state.product.preselectedOffer = offer;
        },
        setReviews(state, reviews) {
            state.reviews = reviews;
        },
        setExitsReview(state, exitsReview) {
            state.reviews.exits_review = exitsReview;
        }
    },
    actions: {
        initialize({ commit, dispatch }, data) {
            dispatch('setDefaultData', data);
            commit('setColors', data.colors);
            commit('setActions', data.actions);
            commit('setDelivery', data.delivery);
            commit('setSiteId', data.siteId);
            commit('setSignedParameters', data.signedParameters);
        },
        setOffer({ commit, getters }, id) {
            let offer = null;
            let offers = getters.getProduct.offers;
            for (let key in offers) {
                if (offers[key].id == id) {
                    offer = offers[key];
                    break;
                }
            }
            if (offer) {
                commit('setPreselectedOffer', offer);
            }
        },
        async changeColor({ dispatch, getters }, id) {
            if (getters.getProduct.id == id) return;
            try {
                showPreloader();
                const data = await changeColor({
                    url: getters.getActions.changeColor,
                    signedParameters: getters.getSignedParameters,
                    id: id
                })
                dispatch('setDefaultData', data);
            } catch (error) {
                if(error instanceof ResultError){
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при изменении цвета товара:', error);
            } finally {
                closePreloader();
            }
        },
        async addReview({ commit, getters }, form) {
            try {
                showPreloader();
                const result = await addReview(getters.getReviews.actions.add, getters.getProductId, form);
                commit('setExitsReview', true);
            } catch (error) {
                if(error instanceof ResultError){
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при добавлении отзыва:', error);
            } finally {
                closePreloader();
            }
        },
        async addProductVieweded({getters}){
            try {
                addProductVieweded(getters.getProductId, getters.getSiteId);
            } catch (error) {
                console.error('Ошибка при добавлении товара в историю просмотра:', error);
            }
        },
        setDefaultData({ commit }, data) {
            commit('setProduct', data.product);
            commit('setPropertiesDefault', data.propertiesDefault);
            commit('setProperties', data.properties);
            commit('setReviews', data.reviews);
        }
    },
    getters: {
        getProduct: (state) => state.product,
        getProductId: (state) => state.product.id,
        getOfferId: (state) => state.product.preselectedOffer ? state.product.preselectedOffer.id : state.product.id,
        getPropertiesDefault: (state) => state.propertiesDefault,
        getProperties: (state) => state.properties,
        getColors: (state) => state.colors,
        getActions: (state) => state.actions,
        getDelivery: (state) => state.delivery,
        getSignedParameters: (state) => state.signedParameters,
        getPrice: (state) => state.product.preselectedOffer && state.product.preselectedOffer.price.priceValue ? state.product.preselectedOffer.price : state.product.price,
        colorsIsAvailable: (state) => state.colors.length > 0,
        getReviews: (state) => state.reviews,
        reviewsAvailable: (state) => state.reviews !== null,
        getSiteId: (state) => state.siteId,
    }
});

export default store;
