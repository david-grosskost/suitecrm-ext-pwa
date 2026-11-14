<?php

// Entry point pwaIcon (legacy/index.php?entryPoint=pwaIcon), declared by
// legacy/Extension/application/Ext/EntryPointRegistry/pwa.php, open without
// login: the manifest points to it. It serves only the icon of the
// application, nothing else.

declare(strict_types=1);

use SuiteCrmExt\Pwa\Asset;
use SuiteCrmExt\Pwa\Icon;
use SuiteCrmExt\Pwa\Reply;

require_once __DIR__ . '/Settings.php';
require_once __DIR__ . '/ConfigTable.php';
require_once __DIR__ . '/DefaultIcon.php';
require_once __DIR__ . '/Icon.php';
require_once __DIR__ . '/Asset.php';
require_once __DIR__ . '/Reply.php';

$ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? null;
$version = $_GET['v'] ?? null;
Reply::send(Asset::response(
    Icon::forSuiteCrm()->current(),
    Icon::TYPE,
    'pwaIcon',
    is_string($ifNoneMatch) ? $ifNoneMatch : null,
    is_string($version) ? $version : null,
));
