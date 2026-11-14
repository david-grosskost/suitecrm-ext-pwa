<?php

declare(strict_types=1);

namespace SuiteCrmExt\Pwa\Tests;

use PHPUnit\Framework\TestCase;
use SuiteCrmExt\Pwa\DefaultIcon;
use SuiteCrmExt\Pwa\Icon;

/** The icon in use: the one chosen in the administration, else the default one. */
final class IconTest extends TestCase
{
    public function testWithoutAnIconChosenTheDefaultOneIsUsed(): void
    {
        $current = (new Icon(new MemorySettings()))->current();

        self::assertFalse($current['custom']);
        self::assertSame(DefaultIcon::SIZE, $current['size']);
        self::assertSame(DefaultIcon::icon()['data'], $current['data']);
        self::assertStringStartsWith('default-', $current['version']);
    }

    public function testTheDefaultIconIsASquarePngOf512Pixels(): void
    {
        $image = getimagesizefromstring(DefaultIcon::icon()['data']);

        self::assertIsArray($image);
        self::assertSame([512, 512, 'image/png'], [$image[0], $image[1], $image['mime']]);
    }

    public function testAnIconChosenIsKeptAndGivenBack(): void
    {
        $settings = new MemorySettings();
        $data = str_repeat('png', 50000);
        (new Icon($settings))->save($data, 1024);

        $current = (new Icon($settings))->current();
        self::assertTrue($current['custom']);
        self::assertSame($data, $current['data']);
        self::assertSame(1024, $current['size']);
        self::assertGreaterThan(2, (int) $settings->values['parts'], 'cut in parts that fit the config table');
    }

    public function testANewIconHasANewVersion(): void
    {
        $icon = new Icon(new MemorySettings());
        $icon->save('first', 512);
        $first = $icon->current()['version'];
        $icon->save('second', 512);

        self::assertNotSame($first, $icon->current()['version']);
    }

    public function testRemovingBringsTheDefaultIconBack(): void
    {
        $icon = new Icon(new MemorySettings());
        $icon->save('chosen', 512);

        self::assertTrue($icon->remove());
        self::assertFalse($icon->current()['custom']);
        self::assertFalse($icon->remove(), 'nothing left to remove');
    }

    public function testDamagedSettingsGiveTheDefaultIcon(): void
    {
        $settings = new MemorySettings(['version' => 'x', 'size' => '512', 'parts' => '1', 'part_0000' => '%%%']);

        self::assertFalse((new Icon($settings))->current()['custom']);
    }
}
