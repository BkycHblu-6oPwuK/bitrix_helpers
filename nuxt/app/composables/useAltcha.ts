const ALTCHA_CHALLENGE_PATH = 'altcha/get-challenge'
const WAIT_TIMEOUT_MS = 15_000
const EXPIRY_BUFFER_MS = 30_000

let currentToken: string | null = null
let currentExpiresAt = 0
let solving = false
let waiters: Array<(token: string | null) => void> = []
let startWidget: (() => void) | null = null

/** Забирает токен (одноразово). Следующий НЕ считается заранее */
function consume(buffer = EXPIRY_BUFFER_MS): string | null {
    if (!currentToken) return null

    const fresh = Date.now() < currentExpiresAt - buffer
    const token = fresh ? currentToken : null

    currentToken = null
    currentExpiresAt = 0

    return token
}

/** Запускает challenge + решение, только если токена нет и решение ещё не идёт */
function ensureSolving() {
    if (currentToken || solving || !startWidget) return
    solving = true
    startWidget()
}

export function useAltcha() {
    function setToken(value: string, expires: number) {
        solving = false
        currentToken = value
        currentExpiresAt = expires

        const waiter = waiters.shift()
        if (waiter) waiter(consume(0))

        if (waiters.length) ensureSolving()
    }

    function invalidate() {
        currentToken = null
        currentExpiresAt = 0
    }

    /** Ошибка виджета: освобождаем всех ожидающих, чтобы не висели до таймаута */
    function fail() {
        solving = false
        const pending = waiters
        waiters = []
        pending.forEach((w) => w(null))
    }

    /** Виджет регистрирует функцию запуска */
    function setStart(fn: (() => void) | null) {
        startWidget = fn
        if (!fn) solving = false
    }

    /** Опциональный прогрев: начать считать заранее (например, при фокусе на форму) */
    function prepare() {
        ensureSolving()
    }

    function waitForToken(): Promise<string | null> {
        const token = consume()
        if (token) return Promise.resolve(token)

        return new Promise<string | null>((resolve) => {
            const timer = setTimeout(() => {
                const idx = waiters.indexOf(onToken)
                if (idx !== -1) waiters.splice(idx, 1)
                resolve(null)
            }, WAIT_TIMEOUT_MS)

            const onToken = (tok: string | null) => {
                clearTimeout(timer)
                resolve(tok)
            }

            waiters.push(onToken)
            ensureSolving()
        })
    }

    return {
        challengeUrl: getApiUrl(ALTCHA_CHALLENGE_PATH),
        setToken,
        invalidate,
        fail,
        setStart,
        prepare,
        waitForToken,
    }
}

export async function getAltchaHeader(): Promise<Record<string, string>> {
    const { waitForToken } = useAltcha()
    const token = await waitForToken()
    return token ? { 'X-Altcha-Token': token } : {}
}