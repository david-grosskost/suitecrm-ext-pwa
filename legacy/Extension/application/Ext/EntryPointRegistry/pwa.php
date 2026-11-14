<?php

// Application installable (extension pwa): the entry points of the
// installable application, open without login (auth false): browsers fetch
// the manifest, its icon and the service worker without the session cookie.
// They serve only those three, nothing of the CRM data.

/** @var array<string, array{file: string, auth: bool}> $entry_point_registry Set by SuiteCRM */
$entry_point_registry['pwaManifest'] = [
    'file' => 'custom/include/pwa/ServeManifest.php',
    'auth' => false,
];
$entry_point_registry['pwaIcon'] = [
    'file' => 'custom/include/pwa/ServeIcon.php',
    'auth' => false,
];
$entry_point_registry['pwaWorker'] = [
    'file' => 'custom/include/pwa/ServeWorker.php',
    'auth' => false,
];
