import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {provideHttpClient, withInterceptorsFromDi} from '@angular/common/http';

// pwa: loaded by SuiteCRM 8 when the interface starts (module federation,
// declared by config/extension.php), it makes the CRM installable as an
// application:
// - the manifest link of the page points to the extension's manifest (name of
//   the instance, its icon), instead of SuiteCRM's static one;
// - the theme colour and the iOS icon are added to the page;
// - the service worker is registered for the whole interface (scope: the
//   page's folder, allowed by the worker's Service-Worker-Allowed header).
// All three come from legacy entry points open without login.
const MANIFEST_URL = 'legacy/index.php?entryPoint=pwaManifest';
const ICON_URL = 'legacy/index.php?entryPoint=pwaIcon';
const WORKER_URL = 'legacy/index.php?entryPoint=pwaWorker';
// Colour of the SuiteCRM 8 header, as in the manifest (Manifest::THEME_COLOR).
const THEME_COLOR = '#534d64';

/** The <link rel="…"> of the page, created if missing, pointing to href. */
const setLink = (rel: string, href: string): void => {
    let link = document.head.querySelector<HTMLLinkElement>(`link[rel="${rel}"]`);
    if (!link) {
        link = document.createElement('link');
        link.rel = rel;
        document.head.appendChild(link);
    }
    link.href = href;
};

/** The <meta name="…"> of the page, created if missing, with content. */
const setMeta = (name: string, content: string): void => {
    let meta = document.head.querySelector<HTMLMetaElement>(`meta[name="${name}"]`);
    if (!meta) {
        meta = document.createElement('meta');
        meta.name = name;
        document.head.appendChild(meta);
    }
    meta.content = content;
};

@NgModule({
    declarations: [],
    imports: [CommonModule],
    providers: [provideHttpClient(withInterceptorsFromDi())]
})
export class ExtensionModule {
    // SuiteCRM 8.10.2 creates the module (createNgModule) but never calls
    // init(): the work is done here, when it is loaded at start.
    constructor() {
        setLink('manifest', MANIFEST_URL);
        setLink('apple-touch-icon', ICON_URL);
        setMeta('theme-color', THEME_COLOR);

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register(WORKER_URL, {scope: './'})
                .catch(error => console.warn('pwa : service worker non enregistré', error));
        }
        console.info('pwa : chargé, application installable');
    }

    init(): void {
    }
}
