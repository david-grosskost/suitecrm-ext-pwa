<?php

declare(strict_types=1);

namespace SuiteCrmExt\Pwa;

/**
 * The web app manifest (pwaManifest entry point, ServeManifest.php): what the
 * browser needs to install the CRM as an application. Built at each request:
 * a System Name changed in the administration is taken at once.
 *
 * Its addresses are relative to its own, legacy/index.php: "../" is the root
 * of SuiteCRM 8 (its interface), whatever the folder SuiteCRM is in.
 */
final class Manifest
{
    /** Colour of the SuiteCRM 8 header (Suite 8 theme). */
    public const string THEME_COLOR = '#534d64';

    /** Background of the SuiteCRM 8 pages, shown while the application opens. */
    public const string BACKGROUND_COLOR = '#f5f5f5';

    public const string DEFAULT_NAME = 'SuiteCRM';

    /** Longest name shown under the icon; a longer one gives SHORT_NAME. */
    public const int SHORT_NAME_MAX = 12;

    public const string SHORT_NAME = 'CRM';

    /** The name of the application: the System Name, else "SuiteCRM". */
    public static function name(mixed $systemName): string
    {
        $name = is_string($systemName) ? trim($systemName) : '';

        return $name !== '' ? $name : self::DEFAULT_NAME;
    }

    /** The name under the icon: the name itself when it is short enough. */
    public static function shortName(string $name): string
    {
        return mb_strlen($name) <= self::SHORT_NAME_MAX ? $name : self::SHORT_NAME;
    }

    /**
     * @param array{version: string, size: int, custom: bool} $icon The icon in use (Icon::current())
     *
     * @return array{id: string, lang: string, dir: string, name: string, short_name: string, start_url: string, scope: string, display: string, background_color: string, theme_color: string, icons: list<array{src: string, sizes: string, type: string, purpose: string}>}
     */
    public static function build(string $name, array $icon): array
    {
        $src = 'index.php?entryPoint=pwaIcon&v=' . rawurlencode($icon['version']);
        $sizes = "{$icon['size']}x{$icon['size']}";
        $icons = [['src' => $src, 'sizes' => $sizes, 'type' => Icon::TYPE, 'purpose' => 'any']];
        // The default icon keeps its letters inside the safe zone of the masks
        // of Android; an icon chosen in the administration may not.
        if (!$icon['custom']) {
            $icons[] = ['src' => $src, 'sizes' => $sizes, 'type' => Icon::TYPE, 'purpose' => 'maskable'];
        }

        return [
            'id' => '../',
            'lang' => 'fr',
            'dir' => 'ltr',
            'name' => $name,
            'short_name' => self::shortName($name),
            'start_url' => '../',
            'scope' => '../',
            'display' => 'standalone',
            'background_color' => self::BACKGROUND_COLOR,
            'theme_color' => self::THEME_COLOR,
            'icons' => $icons,
        ];
    }

    /** @param array<string, mixed> $manifest */
    public static function json(array $manifest): string
    {
        return json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
