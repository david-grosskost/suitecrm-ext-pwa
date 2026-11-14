<?php

declare(strict_types=1);

namespace SuiteCrmExt\Pwa\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SuiteCrmExt\Pwa\Worker;

/** The service worker: its scope, and what it keeps. */
final class WorkerTest extends TestCase
{
    /** @return iterable<string, array{string, string}> */
    public static function scripts(): iterable
    {
        yield 'à la racine du site' => ['/legacy/index.php', '/'];
        yield 'dans un dossier' => ['/crm/legacy/index.php', '/crm/'];
    }

    #[DataProvider('scripts')]
    public function testTheScopeIsTheRootOfSuiteCrm(string $scriptName, string $expected): void
    {
        self::assertSame($expected, Worker::scope($scriptName));
    }

    public function testTheCacheIsNamedAfterTheVersion(): void
    {
        self::assertStringContainsString('const CACHE = "suitecrm-pwa-' . Worker::VERSION . '";', Worker::script());
    }

    public function testOnlyTheInterfaceIsCached(): void
    {
        $script = Worker::script();

        self::assertStringContainsString("new URL('dist/', self.registration.scope)", $script);
        self::assertStringContainsString("if (request.method !== 'GET')", $script);
        self::assertSame(1, substr_count($script, 'cache.put('), 'one place only puts in the cache');
    }

    public function testTheOfflinePageIsInFrench(): void
    {
        $page = Worker::offlinePage();

        self::assertStringContainsString('<html lang="fr">', $page);
        self::assertStringContainsString('Hors connexion', $page);
        self::assertStringContainsString('Réessayer', $page);
        self::assertStringContainsString(json_encode($page, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), Worker::script());
    }
}
