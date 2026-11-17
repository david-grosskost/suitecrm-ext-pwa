# Application installable

Extension SuiteCRM 8 : le CRM **s'installe comme une application** (PWA) sur téléphone et
sur ordinateur, avec le nom de l'instance et son icône. Il s'ouvre alors dans sa propre
fenêtre, sans barre d'adresse, et affiche une page « Hors connexion » quand le réseau
manque.

## Utilisation

- **Installer** : voir « Installer sur un téléphone ou un ordinateur » plus bas.
- **Nom** : le nom système de l'instance (**Administration › Paramètres système**,
  « SuiteCRM » par défaut), repris aussitôt ; son enregistrement demande
  upstream-fixes 0.2.1 ou plus (bug de SuiteCRM 8). Sous l'icône, « CRM » s'il dépasse
  12 caractères.
- **Icône** : **Administration › Application installable › Nom et icône de
  l'application** : une image PNG carrée de 512 × 512 px au moins (1 Mo au plus),
  **Enregistrer**. **Revenir à l'icône par défaut** rend l'icône neutre « CRM ». Réservé
  aux administrateurs.

## Installer sur un téléphone ou un ordinateur

Se connecter au CRM, **recharger la page une fois** (le navigateur enregistre alors le
service worker), puis :

| Appareil | Navigateur | Où |
|---|---|---|
| Android | Chrome | menu **⋮** › **Ajouter à l'écran d'accueil** › **Installer** (ou le bandeau « Installer l'application » en bas de l'écran) |
| Android (Samsung) | Samsung Internet | icône d'installation (flèche vers le bas) dans la barre d'adresse, ou menu **≡** › **Ajouter la page à** › **Écran d'accueil** |
| iPhone, iPad | Safari | **Partager** › **Sur l'écran d'accueil** |
| Ordinateur | Chrome, Edge | icône **Installer** (écran avec une flèche) à droite de la barre d'adresse |

L'icône et le nom de l'instance apparaissent sur l'écran d'accueil ; le CRM s'ouvre en
plein écran, sans barre d'adresse. Vérifié sur un Samsung Galaxy A55 (Chrome).

- **« Ajouter un raccourci » au lieu d'« Installer »** : le navigateur n'a pas encore vu le
  manifeste ou le service worker. Recharger la page connecté, vérifier que l'extension est
  active (`pwa` dans `EXTENSIONS`) et que le site est en HTTPS.
- **Nom ou icône changés dans l'administration** : une application déjà installée les
  reprend à sa prochaine mise à jour (le navigateur relit le manifeste à l'ouverture,
  environ une fois par jour, parfois avec une confirmation) ; pour les voir tout de suite,
  désinstaller puis réinstaller l'application.

## Fonctionnement

- Un **module Angular**, que SuiteCRM 8 charge au démarrage sans recompiler son front,
  pointe la page vers le manifeste de l'extension (à la place du `site.webmanifest` figé
  de SuiteCRM), ajoute la couleur du thème et l'icône iOS, et enregistre le service
  worker.
- Trois **points d'entrée** de SuiteCRM, ouverts sans connexion (les navigateurs les
  demandent sans le cookie de session), ne servent que ceci :
  - `legacy/index.php?entryPoint=pwaManifest` : le manifeste (nom, icône, couleurs),
    construit à chaque demande ;
  - `legacy/index.php?entryPoint=pwaIcon` : l'icône, renvoyée vers son adresse avec
    version (`…&v=<version>`), gardée un an en cache ;
  - `legacy/index.php?entryPoint=pwaWorker` : le service worker, autorisé à couvrir tout
    SuiteCRM 8 (en-tête `Service-Worker-Allowed`).
- Le **service worker** ne garde que l'interface (`dist/` : le front construit, les images
  et polices du thème), servie depuis le cache et rafraîchie derrière. **Jamais les données
  du CRM** : appels d'API, pages de l'ancienne interface et fichiers passent toujours par
  le réseau. Il n'y a pas de mode hors ligne.
- L'icône choisie est gardée **en base**, dans la table `config` de SuiteCRM (catégorie
  `pwa`, découpée en morceaux) : comprise dans la sauvegarde, aucun fichier à écrire.
  L'icône par défaut est dans le code (`DefaultIcon.php`).
- L'extension passe le scanner du Chargeur de module : aucune fonction de fichier, aucun
  `echo` (la page passe par Smarty, les réponses par `print`).

## Un service worker survit à son instance

C'est le navigateur qui garde le service worker, pas le serveur. Il reste donc en place
quand l'instance change sous lui : extension désinstallée, instance reconstruite de zéro,
base effacée — le navigateur continue de servir l'interface depuis son cache et d'appeler
un point d'entrée qui n'existe peut-être plus.

Ça se manifeste par des comportements qui n'ont aucun sens : des écrans d'une version
précédente, des erreurs sur des adresses qui répondent correctement quand on les ouvre à
la main, ou un symptôme qu'on croit avoir corrigé et qui revient.

**Après avoir détruit ou reconstruit une instance**, vider le site dans le navigateur :
F12 → **Application** → **Storage** → *Clear site data*. Fermer ensuite **tous** les
onglets du site. Un simple F5 ne suffit pas : un service worker continue de contrôler les
pages déjà ouvertes jusqu'à ce que la dernière soit fermée, même après sa désinscription.

Ça vaut surtout pour une machine où l'on crée des démos à la chaîne sur la même adresse :
le service worker de la démo précédente est encore là quand la suivante démarre.

## Pour les développeurs

| Fichier | Rôle |
|---|---|
| `legacy/include/pwa/Manifest.php` | le manifeste : nom, nom court, icônes |
| `legacy/include/pwa/Worker.php` | le service worker, sa portée et la page « Hors connexion » |
| `legacy/include/pwa/Icon.php`, `DefaultIcon.php` | l'icône choisie, ou celle par défaut |
| `legacy/include/pwa/ConfigTable.php`, `Settings.php` | les réglages en base (table `config`) |
| `legacy/include/pwa/Upload.php` | contrôle du fichier envoyé, par son contenu |
| `legacy/include/pwa/Asset.php`, `Reply.php` | réponses des points d'entrée |
| `legacy/include/pwa/Page.php`, `PageView.php`, `page.tpl` | la page d'administration |
| `legacy/include/pwa/SettingsPage.php`, `Serve*.php` | branchement dans SuiteCRM |
| `legacy/Extension/…` | section de l'administration, points d'entrée, libellés |
| `app/` → `public/` | le module Angular et sa version construite (`make front@build`) |

Changer le script du service worker : augmenter `Worker::VERSION`, pour que les navigateurs
le réinstallent et vident l'ancien cache.

```bash
make extension@validate && make quality@lint && make quality@stan && make test@unit && make front@check
```
