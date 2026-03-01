import { getDressing, toggleDressing } from "@/api/catalog";
import { showErrorNotification } from "@/app/notify";
import ResultError from "@/lib/ResultError";


const DressingModule = {
    namespaced: true,
    state: {
        dressingCount: 0,
        isInitialize: false
    },
    mutations: {
        setDressingCount(state, count) {
            state.dressingCount = count;
        },
    },
    actions: {
        async initialize({ commit, state }) {
            if(!state.isInitialize){
                try {
                    const result = await getDressing(false);
                    commit('setDressingCount', result.summary.totalQuantity);
                    state.isInitialize = true;
                } catch (error) {
                    if(error instanceof ResultError){
                        showErrorNotification(error.message);
                    } else {
                        showErrorNotification();
                    }
                    console.error('Ошибка при загрузке примерочной:', error);
                }
            }
        },
        async toggleDressing({ commit }, offerId) {
            try {
                const result = await toggleDressing(offerId, false);
                commit('setDressingCount', result.summary.totalQuantity);
            } catch (error) {
                if(error instanceof ResultError){
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при добавлении товара в примерочную:', error);
            }
        },
    },
    getters: {
        getDressingCount: (state) => state.dressingCount,
    },
};

export default DressingModule;
