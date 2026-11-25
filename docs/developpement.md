# Développement

Les commandes `make`, la CI et le format des extensions viennent de
[suitecrm-devkit](https://github.com/david-grosskost/suitecrm-devkit#documentation), à la
version de `composer.json`. Cette page ne décrit que ce qui est propre à l'extension.

## Modifier le module Angular

Après toute modification de `app/` : `make front@build`, puis commiter `app/` **et**
`public/` ensemble. La CI recompile le module (`make front@check`) et refuse un `public/`
qui ne correspond pas à `app/`.

## Modifier le service worker

Augmenter `Worker::VERSION` à chaque changement de son script : les navigateurs le
réinstallent alors et vident l'ancien cache.

## Vérifier

```bash
make extension@validate && make quality@lint && make quality@stan && make test@unit
make front@check
make package@build && make package@scan
```

## Tester sur une instance locale

1. `make package@build`, puis sur l'instance locale de suitecrm-core, **Administration →
   Chargeur de module** : **Choisir un fichier** (`build/pwa-<version>.zip`),
   **Télécharger**, **Installer**.
2. Recharger avec Ctrl+F5, puis F12 → **Application** : le manifeste doit montrer le nom et
   l'icône de l'instance, et un service worker doit être actif.
3. Changer l'icône dans **Administration › Application installable**, vérifier le manifeste.
4. Dans suitecrm-core : `make test@regression`.

Entre deux essais sur une instance reconstruite : vider le site dans le navigateur (voir
[Dépannage](depannage.md)).

## Publier une version

Pull request relue, puis merge (squash) : release-please ouvre la pull request
« chore(main): release x.y.z ». La merger crée le tag, la release et son paquet. Penser à la
`version` de `config/extension.php`, affichée par SuiteCRM. suitecrm-core la récupère avec
`make composer@update PACKAGES="david-grosskost/suitecrm-ext-pwa"`.

Mettre à jour `README.md` et `docs/` dans la même pull request que le changement qu'ils
décrivent.
