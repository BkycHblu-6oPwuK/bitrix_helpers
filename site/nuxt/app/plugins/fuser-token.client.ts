export default defineNuxtPlugin((nuxtApp) => {
  const { token } = useFuser()
  console.log('Fuser token plugin initialized', token.value)
  // Добавляем токен в каждый запрос
  nuxtApp.$fetch = $fetch.create({
    onRequest({ options }) {
      if (!options.headers) options.headers = {};

      if (token.value) {
        options.headers['X-Fuser-Token'] = token.value;
      }
    },

    onResponse({ response }) {
      const newToken = response.headers.get('X-New-Fuser-Token');
      if (newToken) {
        token.value = newToken;
      }
    },

    onResponseError({ response }) {
      if (response.status === 401) {
        token.value = null;
      }
    }
  });
});
