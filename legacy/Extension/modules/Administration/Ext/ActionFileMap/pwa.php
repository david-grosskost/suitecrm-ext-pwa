<?php

// Application installable (extension pwa): the admin page,
// index.php?module=Administration&action=Pwa (key in lower case, as SuiteCRM
// looks it up).

/** @var array<string, string> $action_file_map Set by SuiteCRM */
$action_file_map['pwa'] = 'custom/include/pwa/SettingsPage.php';
