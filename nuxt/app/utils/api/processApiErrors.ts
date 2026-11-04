import type { ApiErrors, ErrorItem, AppError } from '~/types/api'

const DEFAULT_ERROR_MESSAGE = 'Произошла непредвиденная ошибка, попробуйте позже';

export const isCsrfError = (e: any) => e?.statusCode === 403 && e?.statusMessage === 'CSRF Token Mismatch'

export function processApiErrors(error: unknown): ApiErrors {
    if (!error || typeof error !== 'object') {
        return { _general: DEFAULT_ERROR_MESSAGE }
    }

    const err = error as Record<string, unknown>

    const data = err.data && typeof err.data === 'object'
        ? err.data as Record<string, unknown>
        : null

    const errorData = Array.isArray(err.errors)
        ? err.errors as ErrorItem[]
        : Array.isArray(data?.errors)
            ? data.errors as ErrorItem[]
            : null

    if (errorData?.length) {
        const errors: ApiErrors = {}

        for (const item of errorData) {
            if (typeof item.message !== 'string') {
                continue
            }

            if (item.code) {
                errors[String(item.code)] = item.message
            } else {
                errors._general = item.message
            }
        }

        if (Object.keys(errors).length) {
            return errors
        }
    }

    if (err.cause) {
        const causeErrors = processApiErrors(err.cause)

        if (causeErrors) {
            return causeErrors
        }
    }

    if (typeof err.message === 'string' && err.message) {
        if (/^\s*\[\w+\]\s+"[^"]+"\s*:/.test(err.message)) {
            return { _general: DEFAULT_ERROR_MESSAGE }
        }

        return { _general: err.message }
    }

    return { _general: DEFAULT_ERROR_MESSAGE }
}

export function createErrorFromApi(error: unknown): AppError {
    const source =
        error instanceof Error
            ? error
            : null

    const raw = error && typeof error === 'object'
        ? error as Record<string, unknown>
        : {}

    const data = raw.data && typeof raw.data === 'object'
        ? raw.data as Record<string, unknown>
        : {}

    const errors = Array.isArray(raw.errors)
        ? raw.errors as ErrorItem[]
        : Array.isArray(data.errors)
            ? data.errors as ErrorItem[]
            : undefined

    const message =
        source?.message ||
        (typeof raw.message === 'string' ? raw.message : '') ||
        errors?.[0]?.message ||
        DEFAULT_ERROR_MESSAGE

    const result = new Error(message, {
        cause: source?.cause ?? raw.cause
    }) as AppError

    if (errors) {
        result.errors = errors
    }

    const statusCode = raw.statusCode ?? raw.status ?? (
        raw.response && typeof raw.response === 'object'
            ? (raw.response as Record<string, unknown>).status
            : undefined
    )

    if (typeof statusCode === 'number') {
        result.statusCode = statusCode
    }

    if (typeof raw.statusMessage === 'string') {
        result.statusMessage = raw.statusMessage
    }

    return result
}