<?php

declare(strict_types=1);

namespace SuiteCrmExt\Pwa\Tests;

use PHPUnit\Framework\TestCase;
use SuiteCrmExt\Pwa\Asset;

/** The answer of the icon entry point: sent on to its version, then cached. */
final class AssetTest extends TestCase
{
    private const array ICON = ['version' => 'abc123', 'data' => 'PNG'];

    public function testTheAddressWithoutVersionIsSentOnToTheVersionInUse(): void
    {
        $response = Asset::response(self::ICON, 'image/png', 'pwaIcon', null);

        self::assertSame(302, $response['status']);
        self::assertSame('index.php?entryPoint=pwaIcon&v=abc123', $response['headers']['Location']);
        self::assertSame('no-cache', $response['headers']['Cache-Control']);
    }

    public function testAnOldVersionIsSentOnToo(): void
    {
        self::assertSame(302, Asset::response(self::ICON, 'image/png', 'pwaIcon', null, 'old')['status']);
    }

    public function testTheVersionInUseIsServedAndCachedForAYear(): void
    {
        $response = Asset::response(self::ICON, 'image/png', 'pwaIcon', null, 'abc123');

        self::assertSame(200, $response['status']);
        self::assertSame('PNG', $response['body']);
        self::assertSame('image/png', $response['headers']['Content-Type']);
        self::assertSame('public, max-age=' . Asset::CACHE . ', immutable', $response['headers']['Cache-Control']);
    }

    public function testABrowserThatHasItGetsNotModified(): void
    {
        $response = Asset::response(self::ICON, 'image/png', 'pwaIcon', '"abc123"', 'abc123');

        self::assertSame(304, $response['status']);
        self::assertSame('', $response['body']);
    }
}
