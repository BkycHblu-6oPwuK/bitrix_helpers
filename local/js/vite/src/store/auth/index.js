import { createStore } from 'vuex';
import loginEmail from './modules/loginEmail';
import loginTel from './modules/loginTel';
import regEmail from './modules/regEmail';
import about from './modules/about';

const store = createStore({
    modules: {
        about: about,
        loginEmail: loginEmail,
        loginTel: loginTel,
        regEmail: regEmail,
    },
});

export default store;
