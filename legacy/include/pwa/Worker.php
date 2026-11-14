<?php

declare(strict_types=1);

namespace SuiteCrmExt\Pwa;

/**
 * The service worker (pwaWorker entry point, ServeWorker.php), which makes
 * the CRM installable and shows a "Hors connexion" page when the network is
 * gone.
 *
 * It keeps only the interface: the files of dist/ (the built SuiteCRM 8
 * front, its theme images and fonts), served from the cache and refreshed
 * behind (stale-while-revalidate: a SuiteCRM update is taken at the next
 * load). Never the CRM data: API calls, legacy pages and uploads always go to
 * the network, untouched. There is no offline mode.
 */
final class Worker
{
    /**
     * Version of the worker: a new value makes browsers install it again and
     * empty the cache of the previous one. Change it with the script.
     */
    public const string VERSION = '1';

    /**
     * The widest scope the worker may control (Service-Worker-Allowed): the
     * root of SuiteCRM 8, above legacy/ where the entry point answers.
     *
     * @param string $scriptName SCRIPT_NAME of the request: …/legacy/index.php
     */
    public static function scope(string $scriptName): string
    {
        $root = dirname(dirname($scriptName));

        return rtrim(str_replace('\\', '/', $root), '/') . '/';
    }

    public static function script(): string
    {
        $cache = json_encode('suitecrm-pwa-' . self::VERSION, JSON_THROW_ON_ERROR);
        $offline = json_encode(self::offlinePage(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return <<<JS
            // Service worker of the pwa extension (SuiteCRM 8): see Worker.php.
            'use strict';

            const PREFIX = 'suitecrm-pwa-';
            const CACHE = {$cache};
            const OFFLINE = {$offline};
            const DIST = new URL('dist/', self.registration.scope).pathname;

            self.addEventListener('install', () => self.skipWaiting());

            self.addEventListener('activate', (event) => {
              event.waitUntil(
                caches.keys()
                  .then((keys) => Promise.all(keys
                    .filter((key) => key.startsWith(PREFIX) && key !== CACHE)
                    .map((key) => caches.delete(key))))
                  .then(() => self.clients.claim()),
              );
            });

            const offline = () => new Response(OFFLINE, {
              status: 503,
              headers: {'Content-Type': 'text/html; charset=utf-8', 'Cache-Control': 'no-store'},
            });

            self.addEventListener('fetch', (event) => {
              const request = event.request;
              if (request.method !== 'GET') {
                return;
              }
              const url = new URL(request.url);
              if (url.origin !== self.location.origin) {
                return;
              }

              // Pages: always the network; the offline page only when it is unreachable.
              if (request.mode === 'navigate') {
                event.respondWith(fetch(request).catch(offline));
                return;
              }

              // Interface files: from the cache, refreshed behind.
              if (url.pathname.startsWith(DIST)) {
                event.respondWith(caches.open(CACHE).then((cache) => cache.match(request).then((cached) => {
                  const fresh = fetch(request).then((response) => {
                    if (response.ok) {
                      cache.put(request, response.clone());
                    }
                    return response;
                  });
                  if (cached) {
                    event.waitUntil(fresh.catch(() => undefined));
                    return cached;
                  }
                  return fresh;
                })));
              }
            });

            JS;
    }

    /** The page shown when the network is gone, in the colours of SuiteCRM 8. */
    public static function offlinePage(): string
    {
        $theme = Manifest::THEME_COLOR;
        $background = Manifest::BACKGROUND_COLOR;

        return <<<HTML
            <!DOCTYPE html>
            <html lang="fr">
            <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="theme-color" content="{$theme}">
            <title>Hors connexion</title>
            <style>
            body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:{$background};color:#212529;font-family:Lato,-apple-system,"Segoe UI",Roboto,sans-serif}
            main{max-width:22rem;padding:2rem;text-align:center}
            h1{font-size:1.4rem;margin:0 0 .75rem}
            p{margin:0 0 1.5rem;line-height:1.5}
            a{display:inline-block;padding:.6rem 1.4rem;border-radius:.3rem;background:{$theme};color:#fff;text-decoration:none}
            </style>
            </head>
            <body>
            <main>
            <h1>Hors connexion</h1>
            <p>Le CRM a besoin d’une connexion à Internet. Vérifiez votre réseau, puis réessayez.</p>
            <a href="">Réessayer</a>
            </main>
            </body>
            </html>

            HTML;
    }
}
