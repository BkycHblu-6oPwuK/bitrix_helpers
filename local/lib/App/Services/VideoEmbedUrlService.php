<?php

namespace App\Services;

/**
 * Преобразует ссылку на видео (или готовый код iframe) в URL для src у iframe.
 *
 * Поддерживается:
 *  YouTube: youtu.be/ID, youtube.com/watch?v=ID, /embed/ID, /shorts/ID, /live/ID
 *  Rutube:  rutube.ru/video/ID/, rutube.ru/play/embed/ID/, rutube.ru/video/private/ID/?p=KEY
 *
 * Если формат не поддерживается - возвращает пустую строку.
 */
final class VideoEmbedUrlService
{
    private const YOUTUBE_ID_REGEX = '/^[A-Za-z0-9_-]{11}$/';
    private const RUTUBE_ID_REGEX = '[a-f0-9]{32}';

    public function get(string $value): string
    {
        $url = $this->extractUrl($value);
        if ($url === '') {
            return '';
        }

        $parts = parse_url($url);
        if (!is_array($parts) || empty($parts['host'])) {
            return '';
        }

        $host = strtolower(preg_replace('/^(www|m|music)\./i', '', $parts['host']));
        $path = $parts['path'] ?? '';
        parse_str($parts['query'] ?? '', $query);

        return match ($host) {
            'youtube.com', 'youtube-nocookie.com', 'youtu.be' => $this->youtube($host, $path, $query),
            'rutube.ru' => $this->rutube($path, $query),
            default => '',
        };
    }

    /**
     * Достаёт ссылку: если пришёл код iframe - берёт src, иначе использует саму строку.
     */
    private function extractUrl(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (stripos($value, '<iframe') !== false) {
            if (!preg_match('/\bsrc\s*=\s*(["\'])(.*?)\1/is', $value, $m)) {
                return '';
            }
            $value = html_entity_decode(trim($m[2]), ENT_QUOTES | ENT_HTML5);
        }

        if (str_starts_with($value, '//')) {
            return 'https:' . $value;
        }

        if (!preg_match('~^https?://~i', $value)) {
            return 'https://' . $value;
        }

        return $value;
    }

    private function youtube(string $host, string $path, array $query): string
    {
        if ($host === 'youtu.be') {
            $id = explode('/', ltrim($path, '/'))[0];
        } elseif ($path === '/watch') {
            $id = $query['v'] ?? '';
        } elseif (preg_match('~^/(?:embed|shorts|live|v)/([^/]+)~', $path, $m)) {
            $id = $m[1];
        } else {
            return '';
        }

        if (!is_string($id) || $id === 'videoseries' || !preg_match(static::YOUTUBE_ID_REGEX, $id)) {
            return '';
        }

        return 'https://www.youtube.com/embed/' . $id;
    }

    private function rutube(string $path, array $query): string
    {
        $pattern = '~^/(?:video(?:/private)?|play/embed|shorts)/(' . static::RUTUBE_ID_REGEX . ')~i';
        if (!preg_match($pattern, $path, $m)) {
            return '';
        }

        $url = 'https://rutube.ru/play/embed/' . strtolower($m[1]) . '/';

        if (str_contains($path, '/private/') && !empty($query['p']) && is_string($query['p'])) {
            $url .= '?p=' . rawurlencode($query['p']);
        }

        return $url;
    }
}
