export interface UserDTO {
    id: number
    name: string
    email: string
    phone?: string
    avatar?: string
}

export interface UserLoginEmailState {
    email: string
    password: string
}

export interface UserLoginPhoneState {
    phone: string
}

export interface UserLoginPhoneCodeState {
    code: string
}

export interface UserRegisterEmailState {
    name: string
    email: string
    password: string
}

export interface UserState {
    user: UserDTO | null
    authMethods: AuthMethod[]
    isLoadingMethods: boolean
    userLoaded: boolean
}

export interface AuthMethod {
    type: string
    authType: 'url' | 'html' | null
    value: string | null
}

export type AuthType = 'email' | 'phone' | 'telegram' | 'yandexoauth' | string

export type AuthMode = 'login' | 'register' | 'phone'

export type AuthMethodsApiResponse = AuthMethod[];

export type LoginApiResponse = {
    userId: number
    accessToken: string
    refreshToken: string
    accessTokenExpired: number
    refreshTokenExpired: number
}

export type RefreshTokensApiResponse = {
    accessToken: string
    refreshToken: string
    accessTokenExpired: number
    refreshTokenExpired: number
}

export type MeApiResponse = {
    user: UserDTO
}
