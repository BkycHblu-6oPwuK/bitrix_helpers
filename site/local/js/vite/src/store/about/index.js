import { createStore } from 'vuex';
import favouriteModule from './modules/favourite.js';
import basketModule from './modules/basket.js';
import aboutModule from './modules/about.js';
import DressingModule from './modules/dressing.js';

// общее хранилище для всего сайта
const store = createStore({
    modules: {
        favourite: favouriteModule,
        basket: basketModule,
        about: aboutModule,
        dressing: DressingModule,
    },
});

store.dispatch('favourite/initialize');

const resizeHandler = () => store.dispatch('updateIsMobile');
window.addEventListener('resize', resizeHandler)

store.unregisterResizeListener = () => {
    window.removeEventListener('resize', resizeHandler);
};

export default store;
