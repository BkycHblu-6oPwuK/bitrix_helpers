import type { FuserTokenResponse } from "~/types/user";

export function useFuser() {
  const token = useCookie<string | null>('fuser_token');

  const init = async () => {
    if (token.value) return;

    const {data} = await useApi<FuserTokenResponse>('/user/login-fuser', {
        method: 'post',
    })
    if(!data.value?.data?.fuserToken) return
    token.value = data.value?.data?.fuserToken;
  };

  return { token, init };
}
