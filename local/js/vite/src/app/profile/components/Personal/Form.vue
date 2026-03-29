<script setup>
import { computed, nextTick, onMounted, onUnmounted, watch } from 'vue';
import { useStore } from 'vuex';
import { vMaska } from "maska/vue"
import { debounce } from '@/common/js/helpers';
import { phoneMask } from '@/common/js/variables';

const store = useStore();
const form = computed(() => store.getters['personal/getForm']);
const isInitialize = computed(() => store.getters['personal/isInitialize']);
const stopWatchers = [];

const updateField = debounce((field) => {
    store.dispatch('personal/updateField', field);
}, 750);

const registerWathers = () => {
    stopWatchers.push(
        watch(() => form.value.name, (newValue, oldValue) => {
            if (newValue !== oldValue) {
                updateField('name')
            }
        }),
        watch(() => form.value.phone, (newValue, oldValue) => {
            if (newValue !== oldValue) {
                updateField('phone')
            }
        }),
        watch(() => form.value.email, (newValue, oldValue) => {
            if (newValue !== oldValue) {
                updateField('email')
            }
        }),
        watch(() => form.value.gender, (newValue, oldValue) => {
            if (newValue !== oldValue) {
                updateField('gender')
            }
        }),
        watch(() => form.value.birthday, (newValue, oldValue) => {
            if (newValue !== oldValue) {
                updateField('birthday')
            }
        }),
    )
}
if (isInitialize.value) {
    onMounted(async () => {
        await nextTick();
        registerWathers();
    })
} else {
    stopWatchers.push(
        watch(isInitialize, async () => {
            await nextTick();
            registerWathers();
        })
    )
}
onUnmounted(() => {
    stopWatchers.forEach(stop => stop())
})
</script>

<template>
    <div class="profile-personal__data">
        <h3 class="profile__subtitle">Личные данные</h3>
        <div class="profile-personal__data-container">
            <div class="profile-personal-input-block">
                <label for="profile-name">Имя</label>
                <div class="profile-personal-input profile-personal__data-name-input">
                    <input id="profile-name" v-model="form.name" type="text" placeholder="Екатерина">
                    <div class="profile-personal__input-error">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M12 4L4 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
                            <path d="M4 4L12 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
                        </svg>
                    </div>
                    <div class="profile-personal__input-success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M3.33325 8.66663L5.99992 11.3333L12.6666 4.66663" stroke="#219653" stroke-width="2"
                                stroke-linecap="square" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="profile-personal-input-block">
                <label for="profile-phone">Номер телефона</label>
                <div class="profile-personal-input profile-personal__data-phone-input">
                    <input id="profile-phone" v-model="form.phone" v-maska="phoneMask" type="text"
                        placeholder="+7 950 335 49 16">
                    <div class="profile-personal__input-error">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M12 4L4 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
                            <path d="M4 4L12 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
                        </svg>
                    </div>
                    <div class="profile-personal__input-success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M3.33325 8.66663L5.99992 11.3333L12.6666 4.66663" stroke="#219653" stroke-width="2"
                                stroke-linecap="square" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="profile-personal-input-block">
                <label for="profile-email">E-mail</label>
                <div class="profile-personal-input profile-personal__data-email-input">
                    <input id="profile-email" v-model="form.email" type="email" placeholder="ekaterina.live@mail.ru">
                    <div class="profile-personal__input-error">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M12 4L4 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
                            <path d="M4 4L12 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
                        </svg>
                    </div>
                    <div class="profile-personal__input-success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M3.33325 8.66663L5.99992 11.3333L12.6666 4.66663" stroke="#219653" stroke-width="2"
                                stroke-linecap="square" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="profile-personal-input-block profile-personal-input-block_replaced">
                <label for="profile-birthday">Дата рождения</label>
                <div class="profile-personal-input profile-personal__data-birthday-input">
                    <input id="profile-birthday" v-model="form.birthday" v-maska="'## / ## / ####'" type="text"
                        placeholder="26 / 12 / 1994">
                    <div class="profile-personal__input-error">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M12 4L4 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
                            <path d="M4 4L12 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
                        </svg>
                    </div>
                    <div class="profile-personal__input-success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M3.33325 8.66663L5.99992 11.3333L12.6666 4.66663" stroke="#219653" stroke-width="2"
                                stroke-linecap="square" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="profile-radio-block">
                <label for="profile-gender">Выберите свой пол</label>
                <div class="profile-radio-button profile-personal__data-woman">
                    <input id="profile-woman" v-model="form.gender" type="radio" name="gender" value="woman"
                        class="radio-input">
                    <label for="profile-woman">Женщина</label>
                </div>
            </div>
            <div class="profile-radio-button profile-personal__data-man">
                <input id="profile-man" v-model="form.gender" type="radio" name="gender" value="man"
                    class="radio-input">
                <label for="profile-man">Мужчина</label>
            </div>
            <div class="profile-radio-button profile-personal__data-no-gender">
                <input id="profile-no-gender" v-model="form.gender" type="radio" name="gender" value="null"
                    class="radio-input">
                <label for="profile-no-gender">Неважно</label>
            </div>

        </div>
    </div>
</template>