<?php

// Application installable (extension pwa): its section on the Administration
// page, with a link to its settings page.

/** @var array<array-key, array<int, mixed>> $admin_group_header Set by SuiteCRM */
$admin_option_defs = [
    'Administration' => [
        'pwa' => [
            'icon_AdminThemes',
            'LBL_PWA_ADMIN_TITLE',
            'LBL_PWA_ADMIN_DESC',
            './index.php?module=Administration&action=Pwa',
            'admin-themes',
        ],
    ],
];
$admin_group_header[] = [
    'LBL_PWA_ADMIN_HEADER',
    '',
    false,
    $admin_option_defs,
    'LBL_PWA_ADMIN_HEADER_DESC',
    'module_settings',
];
