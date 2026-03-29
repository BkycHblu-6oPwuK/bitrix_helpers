const aboutModule = {
    state: {
        isMobile: window.innerWidth < 768,
    },
    mutations: {
        setIsMobile(state, isMobile) {
            state.isMobile = isMobile;
        }
    },
    actions: {
        updateIsMobile({ commit }) {
            commit('setIsMobile', window.innerWidth < 768);
        }
    },
    getters: {
        isMobile: (state) => state.isMobile
    }
};

export default aboutModule;
