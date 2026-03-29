import { defineStore } from 'pinia'
import type {
    AuthMethod,
    AuthMethodsApiResponse,
    AuthType,
    LoginApiResponse,
    MeApiResponse,
    RefreshTokensApiResponse,
    UserDTO,
    UserState
} from '~/types/user'

export const useUserStore = defineStore('user', {
    state: (): UserState => ({
        user: null,
        authMethods: [],
        isLoadingMethods: false,
        userLoaded: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.user,

        hasEmailAuth: (state) =>
            state.authMethods.some(m => m.type === 'email'),

        hasPhoneAuth: (state) =>
            state.authMethods.some(m => m.type === 'phone'),

        socialAuthMethods: (state): AuthMethod[] =>
            state.authMethods.filter(m => !['email', 'phone'].includes(m.type)),

        defaultAuthMode(state) {
            if (state.authMethods.some(m => m.type === 'email')) return 'login'
            if (state.authMethods.some(m => m.type === 'phone')) return 'phone'
            return 'login'
        },
        hasAnyAuthMethod(): boolean {
            return this.authMethods.length > 0
        },

        fullName: (state) => state.user?.name ?? null,
        email: (state) => state.user?.email ?? null,
        userId: (state) => state.user?.id ?? null,
    },

    actions: {
        setUser(user: UserDTO | null) {
            this.user = user
        },

        async login(type: AuthType, values: Record<string, any>) {
            const response = await useApiFetch<LoginApiResponse>('user/login', {
                method: 'post',
                body: { type, ...values },
            })

            if (response.data?.user) {
                this.setUser(response.data.user)
            }

            return response
        },

        async register(type: AuthType, values: Record<string, any>) {
            const response = await useApiFetch<LoginApiResponse>('user/register', {
                method: 'post',
                body: { type, ...values },
            })

            if (response.data?.user) {
                this.setUser(response.data.user)
            }

            return response
        },

        async refreshTokens() {
            const response = await useApiFetch<RefreshTokensApiResponse>('user/refresh', {
                method: 'post',
            })
            if (response.data?.user) {
                this.setUser(response.data.user)
            }

            return response
        },

        async loadAuthMethods() {
            if(this.isLoadingMethods) return;
            this.isLoadingMethods = true
            try {
                const response = await useApiFetch<AuthMethodsApiResponse>('user/methods', {
                    method: 'get'
                })
                this.authMethods = response.data || []
            } finally {
                this.isLoadingMethods = false
            }
        },

        async logout() {
            try {
                await useApiFetch('user/logout', { method: 'post' })
            } finally {
                this.setUser(null)
            }
        },

        async loadUser() {
            if(this.userLoaded) return;
            this.userLoaded = true
            
            try {
                const response = await useApiFetch<MeApiResponse>('/user/me', {
                    method: 'get'
                })
                if (response?.data?.user) {
                    this.setUser(response.data.user)
                }
            } catch (e: any) {
                // 401 - это нормально, пользователь просто не авторизован
                // Не сбрасываем флаг userLoaded, чтобы не было повторных запросов
                if (e?.statusCode !== 401) {
                    // При реальных ошибках сбрасываем флаг для повторной попытки
                    this.userLoaded = false
                    console.error('Error loading user:', e)
                }
            }
        }
    },
})
