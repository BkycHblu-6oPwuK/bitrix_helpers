export const useBootstrap = () => {
    async function initClient() {
        if (!import.meta.client) return;
        if (window.__BOOTSTRAP_CLIENT_DONE__) return;
        window.__BOOTSTRAP_CLIENT_DONE__ = true;
        const favourite = useFavouriteStore();
        const basketStore = useBasketStore();
        await Promise.all([
            favourite.load(),
            basketStore.fetchIds()
        ]);
    }

    async function initServer() {
        if (!import.meta.client) {
            const userStore = useUserStore();
            const menuStore = useMenuStore();
            await Promise.all([userStore.loadUser(), menuStore.load()]);
        }
    }

    return { initClient, initServer }
};