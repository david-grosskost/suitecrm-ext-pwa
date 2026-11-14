<?php

// Entry point pwaManifest (legacy/index.php?entryPoint=pwaManifest), declared
// by legacy/Extension/application/Ext/EntryPointRegistry/pwa.php, open without
// login: browsers fetch the manifest without the session cookie. It gives
// only the name of the instance and the address of its icon.

declare(strict_types=1);

use SuiteCrmExt\Pwa\Icon;
use SuiteCrmExt\Pwa\Manifest;
use SuiteCrmExt\Pwa\Reply;

require_once __DIR__ . '/Settings.php';
require_once __DIR__ . '/ConfigTable.php';
require_once __DIR__ . '/DefaultIcon.php';
require_once __DIR__ . '/Icon.php';
require_once __DIR__ . '/Manifest.php';
require_once __DIR__ . '/Reply.php';

// System Name of Administration → System Settings.
$administration = BeanFactory::newBean('Administration');
$systemName = null;
if ($administration instanceof Administration) {
    $administration->retrieveSettings('system');
    $systemName = $administration->settings['system_name'] ?? null;
}

$manifest = Manifest::build(Manifest::name($systemName), Icon::forSuiteCrm()->current());
Reply::send(Reply::text('application/manifest+json', Manifest::json($manifest)));
