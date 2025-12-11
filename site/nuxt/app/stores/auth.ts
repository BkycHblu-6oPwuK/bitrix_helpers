import { defineStore } from 'pinia'
import type { AuthMethod, AuthMode, AuthState, userDTO } from '~/types/auth'

export const useAuthStore = defineStore('auth', {
    state: (): AuthState => ({
        user: null,
        accessToken: null,
        refreshToken: null,
        isAuthenticated: false,
        authMethods: [],
        isLoadingMethods: false,
    }),

    getters: {
        /**
         * Проверка доступности email авторизации
         */
        hasEmailAuth(): boolean {
            return this.authMethods.some(m => m.type === 'email')
        },

        /**
         * Проверка доступности phone авторизации
         */
        hasPhoneAuth(): boolean {
            return this.authMethods.some(m => m.type === 'phone')
        },

        /**
         * Получение списка социальных методов авторизации
         */
        socialAuthMethods(): AuthMethod[] {
            return this.authMethods.filter(m => !['email', 'phone'].includes(m.type))
        },

        /**
         * Проверка наличия любых методов авторизации
         */
        hasAnyAuthMethod(): boolean {
            return this.authMethods.length > 0
        },

        /**
         * Режим авторизации по умолчанию
         */
        defaultAuthMode(): AuthMode {
            if (this.hasEmailAuth) return 'login'
            if (this.hasPhoneAuth) return 'phone'
            return 'login'
        },
    },

    actions: {
        /**
         * Загрузка доступных методов авторизации
         */
        async loadAuthMethods() {
            this.isLoadingMethods = true
            try {
                const { useApiFetch } = await import('~/composables/useApi')
                const response = await useApiFetch<AuthMethod[]>('user/methods', {
                    method: 'get'
                })

                this.authMethods = response.data
            } catch (error) {
                console.error('Failed to load auth methods:', error)
                throw error
            } finally {
                this.isLoadingMethods = false
            }
        },

        /**
         * Установка данных пользователя после успешной авторизации
         */
        setAuthData(data: {
            user?: userDTO
            accessToken?: string
            refreshToken?: string
            accessTokenExpired?: number
            refreshTokenExpired?: number
        }) {
            if (data.user) {
                this.user = data.user
            }
            this.isAuthenticated = true

            if (data.accessToken) {
                this.accessToken = data.accessToken
                const accessTokenCookie = useCookie('accessToken', {
                    maxAge: data.accessTokenExpired || 60 * 60 * 24 * 7,
                    sameSite: 'lax',
                    secure: process.env.NODE_ENV === 'production',
                    httpOnly: false,
                })
                accessTokenCookie.value = data.accessToken
            }

            if (data.refreshToken) {
                this.refreshToken = data.refreshToken
                const refreshTokenCookie = useCookie('refreshToken', {
                    maxAge: data.refreshTokenExpired || 60 * 60 * 24 * 30,
                    sameSite: 'lax',
                    secure: process.env.NODE_ENV === 'production',
                    httpOnly: false,
                })
                refreshTokenCookie.value = data.refreshToken
            }
        },

        /**
         * Выход из системы
         */
        async logout(logoutFromBitrix: boolean = true) {
            try {
                const { useApiFetch } = await import('~/composables/useApi')
                await useApiFetch('user/logout', {
                    method: 'post',
                    body: {
                        refreshToken: this.refreshToken,
                        logoutFromBitrix,
                    }
                })
            } catch (error) {
                console.error('Logout error:', error)
            } finally {
                this.clearAuthData()
            }
        },

        /**
         * Очистка данных авторизации
         */
        clearAuthData() {
            this.user = null
            this.accessToken = null
            this.refreshToken = null
            this.isAuthenticated = false

            const accessTokenCookie = useCookie('accessToken')
            const refreshTokenCookie = useCookie('refreshToken')

            accessTokenCookie.value = null
            refreshTokenCookie.value = null
        },

        /**
         * Обновление токенов
         */
        async refreshTokens() {
            if (!this.refreshToken) {
                throw new Error('No refresh token available')
            }

            try {
                const { useApiFetch } = await import('~/composables/useApi')
                const response = await useApiFetch<{
                    accessToken: string
                    refreshToken: string
                }>('user/refresh', {
                    method: 'post',
                    body: {
                        refreshToken: this.refreshToken,
                    }
                })

                if (response.status === 'success' && response.data) {
                    this.accessToken = response.data.accessToken
                    this.refreshToken = response.data.refreshToken

                    const accessTokenCookie = useCookie('accessToken', {
                        maxAge: 60 * 60 * 24 * 7,
                        sameSite: 'lax',
                        secure: process.env.NODE_ENV === 'production',
                    })
                    accessTokenCookie.value = response.data.accessToken

                    if (response.data.refreshToken) {
                        const refreshTokenCookie = useCookie('refreshToken', {
                            maxAge: 60 * 60 * 24 * 30,
                            sameSite: 'lax',
                            secure: process.env.NODE_ENV === 'production',
                        })
                        refreshTokenCookie.value = response.data.refreshToken
                    }
                }
            } catch (error) {
                console.error('Token refresh failed:', error)
                this.clearAuthData()
                throw error
            }
        },

        /**
         * Инициализация состояния авторизации из cookies
         */
        initFromStorage() {
            const accessTokenCookie = useCookie<string | null>('accessToken')
            const refreshTokenCookie = useCookie<string | null>('refreshToken')

            if (accessTokenCookie.value) {
                this.accessToken = accessTokenCookie.value
                this.isAuthenticated = true
            }

            if (refreshTokenCookie.value) {
                this.refreshToken = refreshTokenCookie.value
            }
        },
    },
})
