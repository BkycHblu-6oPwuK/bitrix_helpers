import { getQuestions } from '@/api/profile';
import ResultError from '@/lib/ResultError';
import { showErrorNotification } from '@/app/notify';
import { closePreloader, showPreloader } from '@/app/preloader';

const question = {
    namespaced: true,
    state: {
        isInitialize: false,
        questions: {}
    },
    mutations: {
        setIsInitialize(state){
            state.isInitialize = true;
        },
        setQuestions(state, questions){
            state.questions = questions;
        }
    },
    actions: {
        async initialize({getters, commit}){
            if(!getters.isInitialize){
                try {
                    showPreloader();
                    const result = await getQuestions();
                    commit('setQuestions', result.data.questions);
                    commit('setIsInitialize');
                } catch (error){
                    if(error instanceof ResultError){
                        showErrorNotification(error.message);
                    } else {
                        showErrorNotification();
                    }
                    console.error('Ошибка при загрузке вопросов-ответов: ', error)
                } finally {
                    closePreloader();
                }
            }
        }
    },
    getters: {
        isInitialize: (state) => state.isInitialize,
        getQuestions: (state) => state.questions
    }
};

export default question;
