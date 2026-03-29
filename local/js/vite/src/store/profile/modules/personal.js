import { addPreferenceNotification, getPersonal, updateField } from '@/api/profile';
import ResultError from '@/lib/ResultError';
import { showErrorNotification, showSuccessNotification } from '@/app/notify';

const personal = {
    namespaced: true,
    state: {
        isInitialize: false,
        personal: {},
        form: {
            name: '',
            phone: '',
            email: '',
            birthday: '',
            gender: null
        },
        errors: {}
    },
    mutations: {
        setIsInitialize(state) {
            state.isInitialize = true;
        },
        setPersonal(state, personal) {
            state.personal = personal;
        },
        updatePersonalField(state, { field, value }) {
            state.personal[field] = value;
        },
        setForm(state, form) {
            state.form = form;
        },
        setErrors(state, errors) {
            state.errors = errors
        },
        setNotificationChannelEnabled(state, { notificationType, channelType, enabled }) {
            const notifications = state.personal.notifications;
            const notification = notifications.find(n => n.type === notificationType);
            if (!notification) return;
            const channel = notification.channels.find(c => c.type === channelType);
            if (!channel) return;
            channel.isEnable = enabled;
        }
    },
    actions: {
        async initialize({ getters, commit }) {
            if (!getters.isInitialize) {
                try {
                    const result = await getPersonal();
                    const personal = result.data.personal;
                    commit('setPersonal', personal);
                    commit('setForm', {
                        name: personal.name,
                        phone: personal.phone,
                        email: personal.email,
                        birthday: personal.birthday,
                        gender: personal.gender
                    }),
                        commit('setIsInitialize');
                } catch (error) {
                    if (error instanceof ResultError) {
                        showErrorNotification(error.message);
                    } else {
                        showErrorNotification();
                    }
                    console.error('Ошибка при загрузке персональных данных: ', error)
                }
            }
        },
        async updateField({ getters, commit }, field) {
            const form = getters.getForm;
            try {
                let value = form[field];
                if (value instanceof Date) {
                    value = Math.round(value.getTime() / 1000);
                }
                const result = await updateField(field, value);
                commit('updatePersonalField', {
                    field: field,
                    value: result.data.value
                })
                showSuccessNotification('Поле успешно обновлено.')
            } catch (error) {
                if (error instanceof ResultError) {
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при обновлении персональных данных: ', error)
            }
        },
        async addPreferenceNotification({ dispatch }, {
            notification, channel
        }) {
            try {
                dispatch('updateNotificationChannel', { notification, channel })
                const result = await addPreferenceNotification(notification.type, channel.type, channel.isEnable);
            } catch (error) {
                if (error instanceof ResultError) {
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при обновлении уведомлений: ', error)
                channel.isEnable = !channel.isEnable
                dispatch('updateNotificationChannel', { notification, channel })
            }
        },
        updateNotificationChannel({ commit }, { notification, channel }) {
            commit('setNotificationChannelEnabled', {
                notificationType: notification.type,
                channelType: channel.type,
                enabled: channel.isEnable
            });
        }
    },
    getters: {
        isInitialize: (state) => state.isInitialize,
        getPersonal: (state) => state.personal,
        getForm: (state) => state.form,
        getErrors: (state) => state.form,
        errorsIsEmpty: (state) => Object.values(state.errors).length === 0,
    }
};

export default personal;
