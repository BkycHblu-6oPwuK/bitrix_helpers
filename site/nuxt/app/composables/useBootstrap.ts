export const useBootstrap = () => {
    const favourite = useFavouriteStore();
    const { init: initFuser } = useFuser();

    async function init() {
        if (process.client && window.__BOOTSTRAP_DONE__) return;
        if (process.client) window.__BOOTSTRAP_DONE__ = true;
        
        await initFuser();
        await favourite.load();
    }

    init();
};
