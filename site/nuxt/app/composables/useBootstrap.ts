export const useBootstrap = () => {
    const favourite = useFavouriteStore();
    const userStore = useUserStore();
    const basketStore = useBasketStore();

    async function init() {
        if (process.client && window.__BOOTSTRAP_DONE__) return;
        if (process.client) window.__BOOTSTRAP_DONE__ = true;
        
        await userStore.loadUser();
        
        await Promise.all([
            favourite.load(),
            basketStore.fetchIds()
        ]);
    }

    init();
};
