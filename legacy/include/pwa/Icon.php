<?php

declare(strict_types=1);

namespace SuiteCrmExt\Pwa;

/**
 * The icon of the application: the one chosen in the administration, kept in
 * the settings (base64, cut in parts that fit the value column of the config
 * table, a text of 64 KB), else the default one (DefaultIcon). One icon at
 * most: a new one replaces it.
 */
final class Icon
{
    public const string TYPE = 'image/png';

    /** Smallest side accepted, in pixels: what Android asks for its splash screen. */
    public const int MIN_SIZE = 512;

    /** Heaviest icon accepted: 1 MB, far more than a 512 px PNG needs. */
    public const int MAX_BYTES = 1024 * 1024;

    /** Base64 characters per part. */
    private const int PART = 60000;

    public function __construct(private readonly Settings $settings) {}

    public static function forSuiteCrm(): self
    {
        return new self(ConfigTable::forSuiteCrm());
    }

    /**
     * The icon in use: the one chosen, else the default one. Its version
     * changes with the image (its digest).
     *
     * @return array{version: string, data: string, size: int, custom: bool}
     */
    public function current(): array
    {
        return $this->custom() ?? DefaultIcon::icon();
    }

    /**
     * The icon chosen in the administration, or null.
     *
     * @return array{version: string, data: string, size: int, custom: true}|null
     */
    public function custom(): ?array
    {
        $values = $this->settings->read();
        $count = (int) ($values['parts'] ?? '0');
        $size = (int) ($values['size'] ?? '0');
        if ($count < 1 || $size < self::MIN_SIZE) {
            return null;
        }

        $encoded = '';
        for ($part = 0; $part < $count; ++$part) {
            $encoded .= $values[self::part($part)] ?? '';
        }
        $data = base64_decode($encoded, true);
        if ($data === false || $data === '') {
            return null;
        }

        return ['version' => $values['version'] ?? '', 'data' => $data, 'size' => $size, 'custom' => true];
    }

    /**
     * Keeps an icon, already checked (Upload): a square PNG of $size pixels.
     */
    public function save(string $data, int $size): void
    {
        $parts = str_split(base64_encode($data), self::PART);
        $values = [
            'version' => substr(hash('sha256', $data), 0, 16),
            'size' => (string) $size,
            'parts' => (string) count($parts),
        ];
        foreach ($parts as $index => $part) {
            $values[self::part($index)] = $part;
        }
        $this->settings->replace($values);
    }

    /** Takes the icon chosen away: the default one is back. */
    public function remove(): bool
    {
        if ($this->settings->read() === []) {
            return false;
        }
        $this->settings->replace([]);

        return true;
    }

    private static function part(int $index): string
    {
        return sprintf('part_%04d', $index);
    }
}
