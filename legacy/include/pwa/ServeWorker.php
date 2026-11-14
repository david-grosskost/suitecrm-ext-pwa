<?php

// Entry point pwaWorker (legacy/index.php?entryPoint=pwaWorker), declared by
// legacy/Extension/application/Ext/EntryPointRegistry/pwa.php, open without
// login: the service worker of the application (Worker.php). It answers under
// legacy/, so it allows the worker to control the whole of SuiteCRM 8
// (Service-Worker-Allowed: its root).

declare(strict_types=1);

use SuiteCrmExt\Pwa\Manifest;
use SuiteCrmExt\Pwa\Reply;
use SuiteCrmExt\Pwa\Worker;

require_once __DIR__ . '/Manifest.php';
require_once __DIR__ . '/Worker.php';
require_once __DIR__ . '/Reply.php';

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/legacy/index.php';
Reply::send(Reply::text('text/javascript', Worker::script(), [
    'Service-Worker-Allowed' => Worker::scope(is_string($scriptName) ? $scriptName : '/legacy/index.php'),
]));
