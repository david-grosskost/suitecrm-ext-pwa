<?php

declare(strict_types=1);

namespace SuiteCrmExt\Pwa;

/**
 * The file sent by the admin form: the upload error PHP gives, then its
 * content (read by SuiteCRM's UploadFile, which also checks it was just
 * sent): a square PNG of 512 pixels or more, judged by its bytes, not its
 * name.
 */
final class Upload
{
    /**
     * @param mixed $file The entry of $_FILES
     * @param string|false $data Its content, false when SuiteCRM refused it
     *
     * @return array{size: int}|array{error: string} The side in pixels, or the label of the error
     */
    public static function check(mixed $file, string|false $data): array
    {
        if (!is_array($file) || !is_int($file['error'] ?? null)) {
            return ['error' => 'LBL_PWA_NO_FILE'];
        }

        $error = match ($file['error']) {
            UPLOAD_ERR_OK => null,
            UPLOAD_ERR_NO_FILE => 'LBL_PWA_NO_FILE',
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'LBL_PWA_TOO_BIG',
            default => 'LBL_PWA_UPLOAD_FAILED',
        };
        if ($error !== null) {
            return ['error' => $error];
        }
        if ($data === false || $data === '') {
            return ['error' => 'LBL_PWA_UPLOAD_FAILED'];
        }
        if (strlen($data) > Icon::MAX_BYTES) {
            return ['error' => 'LBL_PWA_TOO_BIG'];
        }

        $type = (new \finfo(FILEINFO_MIME_TYPE))->buffer($data);
        $image = @getimagesizefromstring($data);
        if ($type !== Icon::TYPE || $image === false) {
            return ['error' => 'LBL_PWA_BAD_TYPE'];
        }
        [$width, $height] = $image;
        if ($width !== $height) {
            return ['error' => 'LBL_PWA_NOT_SQUARE'];
        }
        if ($width < Icon::MIN_SIZE) {
            return ['error' => 'LBL_PWA_TOO_SMALL'];
        }

        return ['size' => $width];
    }
}
