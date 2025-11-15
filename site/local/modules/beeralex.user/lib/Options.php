<?php
declare(strict_types=1);

namespace Beeralex\User;

use Beeralex\Core\Config\AbstractOptions;

final class Options extends AbstractOptions
{
    public readonly string $jwtSecretKey;   // raw bytes (может содержать бинарные данные)
    public readonly bool $enableJwtAuth;
    public readonly int $jwtTtl;
    public readonly int $jwtRefreshTtl;
    public readonly string $jwtAlgorithm;
    public readonly string $jwtIssuer;

    protected function mapOptions(array $options): void
    {
        $this->enableJwtAuth = $options['BEERALEX_USER_ENABLE_JWT_AUTH'] === 'Y';
        $this->jwtIssuer     = (string)$options['BEERALEX_USER_JWT_ISSUER'];
        $this->jwtAlgorithm  = strtoupper(trim((string)($options['BEERALEX_USER_JWT_ALGORITHM'])));
        $this->jwtTtl        = (int)$options['BEERALEX_USER_JWT_TTL'];
        $this->jwtRefreshTtl = (int)$options['BEERALEX_USER_JWT_REFRESH_TTL'];

        $allowed = ['HS256', 'HS384', 'HS512'];
        if (!in_array($this->jwtAlgorithm, $allowed, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported JWT algorithm "%s". Allowed: %s',
                $this->jwtAlgorithm,
                implode(', ', $allowed)
            ));
        }

        if ($this->enableJwtAuth) {
            $keyInput = (string)($options['BEERALEX_USER_JWT_SECRET_KEY'] ?? '');
            $keyInput = $this->normalizeBase64Input($keyInput);

            if ($keyInput === '') {
                throw new \InvalidArgumentException('JWT secret key is empty while JWT auth is enabled');
            }

            $decoded = base64_decode($keyInput, true);
            if ($decoded === false) {
                throw new \InvalidArgumentException('JWT secret key must be a valid Base64 string');
            }

            $minLen = match ($this->jwtAlgorithm) {
                'HS256' => 32, // 256-bit
                'HS384' => 48, // 384-bit
                'HS512' => 64, // 512-bit
                default => 32,
            };

            if (strlen($decoded) < $minLen) {
                throw new \InvalidArgumentException(sprintf(
                    'JWT secret key is too short for %s. Got %d bytes, need at least %d bytes',
                    $this->jwtAlgorithm,
                    strlen($decoded),
                    $minLen
                ));
            }

            $this->jwtSecretKey = $decoded;
        } else {
            $this->jwtSecretKey = '';
        }
    }

    public function getModuleId(): string
    {
        return 'beeralex.user';
    }

    /**
     * Убирает пробелы/переводы строк и нормализует паддинг, чтобы base64_decode(strict=true) сработал.
     */
    private function normalizeBase64Input(string $in): string
    {
        $trimmed = preg_replace('/\s+/', '', trim($in) ?? '');
        if ($trimmed === null) {
            return '';
        }
        // Добавим '=' до кратности 4, если админка/копипаст отрезала паддинг
        $len = strlen($trimmed);
        if ($len > 0 && ($len % 4) !== 0) {
            $trimmed .= str_repeat('=', 4 - ($len % 4));
        }
        return $trimmed;
    }
}