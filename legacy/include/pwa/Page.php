<?php

declare(strict_types=1);

namespace SuiteCrmExt\Pwa;

/**
 * The admin page (Administration → Application installable): the name and
 * the icon of the application, a field to choose another icon, a button to
 * go back to the default one. What it does and shows, without SuiteCRM:
 * SettingsPage.php plugs it in.
 */
final class Page
{
    /** Legacy action of the page, in the Administration module (ActionFileMap). */
    public const string ACTION = 'Pwa';

    /** Texts of the page, in the Administration labels. */
    public const array LABELS = [
        'LBL_PWA_ADMIN_TITLE',
        'LBL_PWA_PAGE_INTRO',
        'LBL_PWA_NAME',
        'LBL_PWA_NAME_HELP',
        'LBL_PWA_CURRENT',
        'LBL_PWA_DEFAULT',
        'LBL_PWA_ICON',
        'LBL_PWA_ICON_HELP',
        'LBL_PWA_SAVE',
        'LBL_PWA_REMOVE',
        'LBL_PWA_SAVED',
        'LBL_PWA_REMOVED',
        'LBL_PWA_NO_FILE',
        'LBL_PWA_TOO_BIG',
        'LBL_PWA_BAD_TYPE',
        'LBL_PWA_NOT_SQUARE',
        'LBL_PWA_TOO_SMALL',
        'LBL_PWA_UPLOAD_FAILED',
    ];

    /**
     * What a request does: nothing on display, the icon kept on
     * "Enregistrer", the default one back on "Revenir à l'icône par défaut".
     *
     * @param array<array-key, mixed> $post
     * @param array<array-key, mixed> $files
     * @param string|false $data Content of the file sent, as SuiteCRM's UploadFile read it (false: refused or none)
     * @param array<array-key, mixed> $labels
     *
     * @return list<array{string, string}> Messages (type, text)
     */
    public static function handle(array $post, array $files, string|false $data, Icon $icon, array $labels): array
    {
        if (isset($post['pwa_remove'])) {
            $icon->remove();

            return [['notice', self::label($labels, 'LBL_PWA_REMOVED')]];
        }
        if (!isset($post['pwa_save'])) {
            return [];
        }

        $checked = Upload::check($files['pwa_icon'] ?? null, $data);
        if (isset($checked['error'])) {
            return [['error', self::label($labels, $checked['error'])]];
        }
        if ($data === false) {
            return [['error', self::label($labels, 'LBL_PWA_UPLOAD_FAILED')]];
        }
        $icon->save($data, $checked['size']);

        return [['notice', self::label($labels, 'LBL_PWA_SAVED')]];
    }

    /**
     * The form, in SuiteCRM's legacy styles (edit view, buttons), with the
     * name and a preview of the icon in use.
     *
     * @param array{version: string, size: int, custom: bool} $current The icon in use (Icon::current())
     * @param list<array{string, string}> $messages
     * @param array<array-key, mixed> $labels
     */
    public static function html(string $name, array $current, array $messages, array $labels): string
    {
        $html = '';
        foreach ($messages as [$type, $text]) {
            $class = $type === 'error' ? 'error' : 'msg';
            $html .= "<p class=\"{$class}\">" . self::escape($text) . "</p>\n";
        }

        $action = self::ACTION;
        $accept = Icon::TYPE;
        $max = Icon::MAX_BYTES;
        // Texts escaped beforehand, not by closures in the heredoc: the scanner
        // of the Module Loader refuses the calls of a variable ($l()).
        $version = self::escape($current['version']);
        $size = (int) $current['size'];
        // The version of the icon in the address: a new one is not taken from the cache.
        $preview = "<img src=\"index.php?entryPoint=pwaIcon&amp;v={$version}\" alt=\"\" width=\"128\" height=\"128\" style=\"border-radius: 24px; border: 1px solid #ccc;\">"
            . ($current['custom'] ? " <span>{$size} × {$size} px</span>" : ' <em>' . self::text($labels, 'LBL_PWA_DEFAULT') . '</em>');
        $remove = $current['custom']
            ? '<input type="submit" class="button" name="pwa_remove" value="' . self::text($labels, 'LBL_PWA_REMOVE') . '">'
            : '';
        $intro = self::text($labels, 'LBL_PWA_PAGE_INTRO');
        $nameLabel = self::text($labels, 'LBL_PWA_NAME');
        $nameValue = self::escape($name);
        $nameHelp = self::text($labels, 'LBL_PWA_NAME_HELP');
        $currentLabel = self::text($labels, 'LBL_PWA_CURRENT');
        $iconLabel = self::text($labels, 'LBL_PWA_ICON');
        $help = self::text($labels, 'LBL_PWA_ICON_HELP');
        $saveLabel = self::text($labels, 'LBL_PWA_SAVE');

        return $html . <<<HTML
            <p>{$intro}</p>
            <form method="post" enctype="multipart/form-data" action="index.php?module=Administration&amp;action={$action}">
                <input type="hidden" name="module" value="Administration">
                <input type="hidden" name="action" value="{$action}">
                <input type="hidden" name="MAX_FILE_SIZE" value="{$max}">
                <table class="edit view" width="100%" cellspacing="1" cellpadding="0" border="0">
                    <tr>
                        <td scope="row" width="25%">{$nameLabel}</td>
                        <td>
                            <strong>{$nameValue}</strong>
                            <span>{$nameHelp}</span>
                        </td>
                    </tr>
                    <tr>
                        <td scope="row">{$currentLabel}</td>
                        <td>{$preview}</td>
                    </tr>
                    <tr>
                        <td scope="row"><label for="pwa_icon">{$iconLabel}</label></td>
                        <td>
                            <input type="file" id="pwa_icon" name="pwa_icon" accept="{$accept}">
                            <span>{$help}</span>
                        </td>
                    </tr>
                </table>
                <input type="submit" class="button primary" name="pwa_save" value="{$saveLabel}">
                {$remove}
            </form>
            HTML;
    }

    private static function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /** @param array<array-key, mixed> $labels */
    private static function text(array $labels, string $key): string
    {
        return self::escape(self::label($labels, $key));
    }

    /** @param array<array-key, mixed> $labels */
    public static function label(array $labels, string $key): string
    {
        $value = $labels[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : $key;
    }
}
