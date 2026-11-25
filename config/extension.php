<?php

// Application installable (extension pwa): declares its
// Angular module to SuiteCRM 8, which loads it when the interface starts
// (module federation). The module (public/, built from app/ by make
// front@build) is under extensions/Pwa/ in SuiteCRM: the slug
// in PascalCase. Enabled: the extension's files are there only when it is
// installed (Module Loader).

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Container;

if (!isset($container)) {
    return;
}

/** @var Container $container */
$extensions = $container->getParameter('extensions');
$extensions = is_array($extensions) ? $extensions : [];

$extensions['Pwa'] = [
    'remoteEntry' => '../extensions/Pwa/remoteEntry.js',
    'remoteName' => 'Pwa',
    'enabled' => true,
    'extension_name' => 'Application installable',
    'extension_uri' => 'https://github.com/david-grosskost/suitecrm-ext-pwa',
    'description' => 'Le CRM installable comme une application (PWA), avec son nom et son icône.',
    'version' => '0.1.0',
    'author' => 'David Grosskost',
    'author_uri' => 'https://david-grosskost.fr',
    'license' => 'proprietary',
];

$container->setParameter('extensions', $extensions);
