export type Error = {
    code: Number,
    customData: null|Object,
    message: string
}

export type ApiSuccess<T> = { 
    status: 'success'; 
    data: T; 
    errors: [] 
}

export type ApiError = { 
    status: 'error'; 
    data: null; 
    errors: Error[] 
}

export type ApiResponse<T> = ApiSuccess<T> | ApiError