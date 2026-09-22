export const useBootstrap = () => {
    const favourite = useFavouriteStore();
    const userStore = useUserStore();
    const basketStore = useBasketStore();

    async function initClient() {
        if(!import.meta.client) return;
        if (window.__BOOTSTRAP_CLIENT_DONE__) return;
        window.__BOOTSTRAP_CLIENT_DONE__ = true;

        await Promise.all([
            favourite.load(),
            basketStore.fetchIds()
        ]);
    }

    async function initServer() {
        if (!import.meta.client) {
            await Promise.all([userStore.loadUser()]);
        }
    }

    return { initClient, initServer }
};
