<?php

declare(strict_types=1);

namespace SuiteCrmExt\Pwa\Tests;

use PHPUnit\Framework\TestCase;
use SuiteCrmExt\Pwa\DefaultIcon;
use SuiteCrmExt\Pwa\Icon;
use SuiteCrmExt\Pwa\Page;

/** The admin page: what a request does, and what the page shows. */
final class PageTest extends TestCase
{
    private const array SENT = ['pwa_icon' => ['error' => UPLOAD_ERR_OK, 'tmp_name' => '/tmp/php1234']];

    private const array LABELS = ['LBL_PWA_SAVED' => 'Icône enregistrée', 'LBL_PWA_REMOVED' => 'Icône par défaut de retour'];

    public function testDisplayingDoesNothing(): void
    {
        $settings = new MemorySettings();

        self::assertSame([], Page::handle([], [], false, new Icon($settings), self::LABELS));
        self::assertSame([], $settings->values);
    }

    public function testSavingAValidIconKeepsIt(): void
    {
        $icon = new Icon(new MemorySettings());
        $messages = Page::handle(['pwa_save' => '1'], self::SENT, DefaultIcon::icon()['data'], $icon, self::LABELS);

        self::assertSame([['notice', 'Icône enregistrée']], $messages);
        self::assertTrue($icon->current()['custom']);
    }

    public function testSavingAWrongImageKeepsNothing(): void
    {
        $settings = new MemorySettings();
        $messages = Page::handle(['pwa_save' => '1'], self::SENT, Images::wide(), new Icon($settings), self::LABELS);

        self::assertSame([['error', 'LBL_PWA_NOT_SQUARE']], $messages, 'the key when the label is missing');
        self::assertSame([], $settings->values);
    }

    public function testRemovingBringsTheDefaultIconBack(): void
    {
        $icon = new Icon(new MemorySettings());
        $icon->save('chosen', 512);

        self::assertSame([['notice', 'Icône par défaut de retour']], Page::handle(['pwa_remove' => '1'], [], false, $icon, self::LABELS));
        self::assertFalse($icon->current()['custom']);
    }

    public function testThePageShowsTheNameEscaped(): void
    {
        $html = Page::html('Dupont & <Fils>', DefaultIcon::icon(), [], []);

        self::assertStringContainsString('<strong>Dupont &amp; &lt;Fils&gt;</strong>', $html);
    }

    public function testTheDefaultIconCannotBeRemoved(): void
    {
        $html = Page::html('CRM', DefaultIcon::icon(), [], []);

        self::assertStringNotContainsString('pwa_remove', $html);
        self::assertStringContainsString('LBL_PWA_DEFAULT', $html);
    }

    public function testAnIconChosenCanBeRemoved(): void
    {
        $html = Page::html('CRM', ['version' => 'abc', 'size' => 1024, 'custom' => true], [], []);

        self::assertStringContainsString('name="pwa_remove"', $html);
        self::assertStringContainsString('entryPoint=pwaIcon&amp;v=abc', $html);
        self::assertStringContainsString('1024 × 1024 px', $html);
    }
}
