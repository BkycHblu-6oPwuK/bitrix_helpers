const CSRF_METHODS = new Set(['POST', 'PUT', 'PATCH', 'DELETE'])

export function getCsrfHeaders(method: string): Record<string, string> {
    if (!CSRF_METHODS.has(method.toUpperCase())) return {}
    const { csrf, headerName } = useCsrf()
    return { [headerName]: csrf }
}
