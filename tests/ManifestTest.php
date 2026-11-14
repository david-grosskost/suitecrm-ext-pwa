<?php

declare(strict_types=1);

namespace SuiteCrmExt\Pwa\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SuiteCrmExt\Pwa\Manifest;

/** The manifest: name of the instance, its icon, installable. */
final class ManifestTest extends TestCase
{
    private const array DEFAULT_ICON = ['version' => 'default-1', 'size' => 512, 'custom' => false];

    /** @return iterable<string, array{mixed, string}> */
    public static function systemNames(): iterable
    {
        yield 'nom système choisi' => ['CRM Dupont', 'CRM Dupont'];
        yield 'espaces autour' => ['  CRM Dupont ', 'CRM Dupont'];
        yield 'vide' => ['', 'SuiteCRM'];
        yield 'que des espaces' => ['   ', 'SuiteCRM'];
        yield 'absent' => [null, 'SuiteCRM'];
    }

    #[DataProvider('systemNames')]
    public function testTheNameIsTheSystemName(mixed $systemName, string $expected): void
    {
        self::assertSame($expected, Manifest::name($systemName));
    }

    /** @return iterable<string, array{string, string}> */
    public static function shortNames(): iterable
    {
        yield 'court' => ['CRM Dupont', 'CRM Dupont'];
        yield '13 caractères' => ['Éléphant Bleu', 'CRM'];
        yield 'exactement 12' => ['Abcdefghijkl', 'Abcdefghijkl'];
        yield 'long' => ['Transports Martin et Fils', 'CRM'];
    }

    #[DataProvider('shortNames')]
    public function testTheNameUnderTheIconIsShort(string $name, string $expected): void
    {
        self::assertSame($expected, Manifest::shortName($name));
    }

    public function testTheManifestMakesTheCrmInstallable(): void
    {
        $manifest = Manifest::build('CRM Dupont', self::DEFAULT_ICON);

        self::assertSame('CRM Dupont', $manifest['name']);
        self::assertSame('standalone', $manifest['display']);
        self::assertSame('fr', $manifest['lang']);
        // Relative to legacy/index.php: the root of SuiteCRM 8.
        self::assertSame('../', $manifest['start_url']);
        self::assertSame('../', $manifest['scope']);
        self::assertSame(Manifest::THEME_COLOR, $manifest['theme_color']);
    }

    public function testTheDefaultIconIsAlsoMaskable(): void
    {
        $icons = Manifest::build('CRM', self::DEFAULT_ICON)['icons'];

        self::assertSame(['any', 'maskable'], array_column($icons, 'purpose'));
        self::assertSame('index.php?entryPoint=pwaIcon&v=default-1', $icons[0]['src']);
        self::assertSame('512x512', $icons[0]['sizes']);
    }

    public function testAnIconChosenIsNotMaskable(): void
    {
        $icons = Manifest::build('CRM', ['version' => 'abc', 'size' => 1024, 'custom' => true])['icons'];

        self::assertSame(['any'], array_column($icons, 'purpose'));
        self::assertSame('1024x1024', $icons[0]['sizes']);
    }

    public function testTheJsonKeepsAccentsAndSlashes(): void
    {
        $json = Manifest::json(Manifest::build('Société Générale', self::DEFAULT_ICON));

        self::assertStringContainsString('"name": "Société Générale"', $json);
        self::assertStringContainsString('"start_url": "../"', $json);
        self::assertIsArray(json_decode($json, true));
    }
}
