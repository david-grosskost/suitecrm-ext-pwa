<?php

declare(strict_types=1);

namespace SuiteCrmExt\Pwa;

/**
 * The answer of the pwaIcon entry point (ServeIcon.php). The address without
 * version is sent on, never cached, to the address with the version of the
 * icon in use (&v=…), which is cached for a year: the browser downloads an
 * icon once, and a new icon has a new address. (SuiteCRM's .htaccess takes
 * the ETag away, so a 304 can't be relied on.)
 */
final class Asset
{
    /** One year, in seconds: the icon of a version never changes. */
    public const int CACHE = 31536000;

    /**
     * @param array{version: string, data: string} $current
     * @param string|null $version The version asked for (&v=…), if any
     *
     * @return array{status: int, headers: array<string, string>, body: string}
     */
    public static function response(array $current, string $type, string $entryPoint, ?string $ifNoneMatch, ?string $version = null): array
    {
        if ($version !== $current['version']) {
            return [
                'status' => 302,
                'headers' => [
                    'Cache-Control' => 'no-cache',
                    'Location' => "index.php?entryPoint={$entryPoint}&v=" . rawurlencode($current['version']),
                ],
                'body' => '',
            ];
        }

        $etag = '"' . $current['version'] . '"';
        $headers = [
            'Cache-Control' => 'public, max-age=' . self::CACHE . ', immutable',
            'Pragma' => 'public',
            'ETag' => $etag,
            'X-Content-Type-Options' => 'nosniff',
        ];
        if ($ifNoneMatch !== null && trim($ifNoneMatch) === $etag) {
            return ['status' => 304, 'headers' => $headers, 'body' => ''];
        }

        return [
            'status' => 200,
            'headers' => $headers + [
                'Content-Type' => $type,
                'Content-Length' => (string) strlen($current['data']),
            ],
            'body' => $current['data'],
        ];
    }
}
