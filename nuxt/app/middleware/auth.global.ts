// middleware/auth.global.ts
// Пользователь загружается через useBootstrap в app.vue
// Этот middleware может использоваться для защиты маршрутов в будущем
export default defineNuxtRouteMiddleware(async (to, from) => {
  // Здесь можно добавить проверки доступа к защищенным маршрутам
})