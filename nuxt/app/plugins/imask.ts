import { IMaskComponent } from 'vue-imask'

export default defineNuxtPlugin((nuxtApp) => {
    nuxtApp.vueApp.component('IMaskComponent', IMaskComponent)
})