import { createStore } from 'vuex';
import order from './modules/order';
import personal from './modules/personal';
import question from './modules/question';
import dressing from './modules/dressing';

const store = createStore({
    modules: {
        order: order,
        dressing: dressing,
        personal: personal,
        question: question
    },
});

export default store;
