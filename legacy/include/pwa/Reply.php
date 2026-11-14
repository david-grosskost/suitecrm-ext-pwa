<?php

declare(strict_types=1);

namespace SuiteCrmExt\Pwa;

/**
 * Sends the answer of an entry point (manifest, icon, worker) in place of
 * whatever SuiteCRM has started to write.
 */
final class Reply
{
    /** @param array{status: int, headers: array<string, string>, body: string} $response */
    public static function send(array $response): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code($response['status']);
        foreach ($response['headers'] as $name => $value) {
            header("$name: $value");
        }
        if (str_starts_with($response['headers']['Cache-Control'] ?? '', 'public')) {
            // SuiteCRM's session dates it in the past: the cache would not keep it.
            header_remove('Expires');
        }
        // print, the file is the answer (echo is refused by the scanner of the
        // Module Loader).
        print $response['body'];
    }

    /**
     * A text answer, never kept by the cache: the browser asks again each time.
     *
     * @param array<string, string> $headers
     *
     * @return array{status: int, headers: array<string, string>, body: string}
     */
    public static function text(string $type, string $body, array $headers = []): array
    {
        return [
            'status' => 200,
            'headers' => ['Content-Type' => "{$type}; charset=utf-8", 'Cache-Control' => 'no-cache', 'X-Content-Type-Options' => 'nosniff'] + $headers,
            'body' => $body,
        ];
    }
}
