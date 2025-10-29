const about = {
    namespaced: true,
    state: {
        params: {}
    },
    mutations: {
        setParams(state, params){
            state.params = params;
        }
    },
    actions: {
        initialize({commit}, params){
            commit('setParams', params);
        }
    },
    getters: {
        getParams: (state) => state.params
    }
};

export default about;
