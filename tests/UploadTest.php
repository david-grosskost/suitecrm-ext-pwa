<?php

declare(strict_types=1);

namespace SuiteCrmExt\Pwa\Tests;

use PHPUnit\Framework\TestCase;
use SuiteCrmExt\Pwa\DefaultIcon;
use SuiteCrmExt\Pwa\Icon;
use SuiteCrmExt\Pwa\Upload;

/** The icon sent by the admin form, checked by its content. */
final class UploadTest extends TestCase
{
    private const array SENT = ['error' => UPLOAD_ERR_OK, 'tmp_name' => '/tmp/php1234'];

    public function testASquarePngOf512PixelsIsAccepted(): void
    {
        self::assertSame(['size' => 512], Upload::check(self::SENT, DefaultIcon::icon()['data']));
    }

    public function testATooSmallPngIsRefused(): void
    {
        self::assertSame(['error' => 'LBL_PWA_TOO_SMALL'], Upload::check(self::SENT, Images::png()));
    }

    public function testAPngThatIsNotSquareIsRefused(): void
    {
        self::assertSame(['error' => 'LBL_PWA_NOT_SQUARE'], Upload::check(self::SENT, Images::wide()));
    }

    public function testAJpegIsRefused(): void
    {
        self::assertSame(['error' => 'LBL_PWA_BAD_TYPE'], Upload::check(self::SENT, Images::jpeg()));
    }

    public function testATextNamedLikeAnImageIsRefused(): void
    {
        self::assertSame(['error' => 'LBL_PWA_BAD_TYPE'], Upload::check(self::SENT, Images::fake()));
    }

    public function testAFileSuiteCrmRefusedIsNotKept(): void
    {
        self::assertSame(['error' => 'LBL_PWA_UPLOAD_FAILED'], Upload::check(self::SENT, false));
    }

    public function testTooBigForTheExtension(): void
    {
        self::assertSame(['error' => 'LBL_PWA_TOO_BIG'], Upload::check(self::SENT, str_repeat('x', Icon::MAX_BYTES + 1)));
    }

    public function testNoFileChosen(): void
    {
        self::assertSame(['error' => 'LBL_PWA_NO_FILE'], Upload::check(['error' => UPLOAD_ERR_NO_FILE, 'tmp_name' => ''], false));
        self::assertSame(['error' => 'LBL_PWA_NO_FILE'], Upload::check(null, false));
    }

    public function testTooBigForTheServer(): void
    {
        self::assertSame(['error' => 'LBL_PWA_TOO_BIG'], Upload::check(['error' => UPLOAD_ERR_INI_SIZE, 'tmp_name' => ''], false));
        self::assertSame(['error' => 'LBL_PWA_TOO_BIG'], Upload::check(['error' => UPLOAD_ERR_FORM_SIZE, 'tmp_name' => ''], false));
    }

    public function testAnotherUploadError(): void
    {
        self::assertSame(['error' => 'LBL_PWA_UPLOAD_FAILED'], Upload::check(['error' => UPLOAD_ERR_PARTIAL, 'tmp_name' => ''], false));
    }
}
