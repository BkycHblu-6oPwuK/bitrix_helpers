const catalogSectionList = {
    namespaced: true,
    state: {
        items: [],
    },
    mutations: {
        updateItems(state, { items, append = false }) {
            state.items = append ? [...state.items, ...items] : items;
        },
    },
    actions: {
        initialize({ commit }, data) {
            commit('updateItems', { items: data.sections });
        },
    },
    getters: {
        getItems: (state) => state.items,
    }
};

export default catalogSectionList;
