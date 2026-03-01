import { fetchHelper } from "@/api/helper";
import { createStore } from "vuex";

export const createAppStore = (data) => {
    const store = createStore({
        state: () => ({
            id: data.id,
            url: data.url,
            title: data.title,
            description: data.description,
            fields: data.fields,
            form: getDefaultForm(data.fields), // непосредственно заполнение формы, ключом выступает имя поля
            formIdsMap: data.formIdsMap,
            error: data.error,
        }),
        mutations: {
            setId(state, id) {
                state.id = id;
            },
            setUrl(state, url) {
                state.url = url;
            },
            setTitle(state, title) {
                state.title = title;
            },
            setDescription(state, description) {
                state.description = description;
            },
            setFields(state, fields) {
                state.fields = fields;
            },
            setForm(state, form) {
                state.form = form;
            },
            setFormIdsMap(state, formIdsMap) {
                state.formIdsMap = formIdsMap;
            },
            setError(state, error) {
                state.error = error;
            },
        },
        actions: {
            async sendForm({ state, commit }) {
                const formIdsMap = state.formIdsMap;
                const formData = new URLSearchParams();
                formData.append(formIdsMap.ajax, 'Y');
                formData.append(formIdsMap.formId, state.id);
                formData.append(formIdsMap.formApply, 'Y');
                for (let key in state.fields) {
                    const field = state.fields[key];
                    formData.append(formIdsMap[field.name], state.form[field.name] ?? '')
                }
                const response = await fetchHelper({
                    url: state.url,
                    formData: formData, method: 'POST'
                });
                const result = await response.json();;
                if (result.formNewDto) {
                    commit('setFields', result.formNewDto.fields);
                    commit('setFormIdsMap', result.formNewDto.formIdsMap);
                    commit('setError', result.formNewDto.error);
                    if (result.formNewDto.successAdded) {
                        commit('setForm', getDefaultForm(result.formNewDto.fields))
                    }
                }
            }
        }
    });
    return store;
}

function getDefaultForm(fields) {
    const form = {};
    for (let key in fields) {
        const field = fields[key];
        form[field.name] = '';
    }
    return form;
}