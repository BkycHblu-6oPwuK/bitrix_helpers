import type { ApiResponse } from "./api"

export interface AuthMethod {
    type: string
    authType: 'url' | 'html' | null
    value: string | null
}

export interface AuthState {
    user: userDTO | null
    accessToken: string | null
    refreshToken: string | null
    isAuthenticated: boolean
    authMethods: AuthMethod[]
    isLoadingMethods: boolean
}

export interface userDTO {
    id: number
    name: string
    email: string
}

export type AuthMode = 'login' | 'register' | 'phone'
export type AuthMethodsApiResponse = ApiResponse<AuthMethod[]>