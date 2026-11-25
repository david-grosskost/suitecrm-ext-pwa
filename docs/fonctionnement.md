# Fonctionnement

## Fichiers

| Fichier | Rôle |
|---|---|
| `app/` → `public/` | le module Angular et sa version compilée (`make front@build`) |
| `config/extension.php` | déclare le module à SuiteCRM 8 |
| `legacy/include/pwa/Manifest.php` | le manifeste : nom, nom court, icônes, couleurs |
| `legacy/include/pwa/Worker.php` | le service worker, sa portée et la page « Hors connexion » |
| `legacy/include/pwa/Icon.php`, `DefaultIcon.php` | l'icône choisie, ou celle par défaut |
| `legacy/include/pwa/ConfigTable.php`, `Settings.php` | les réglages en base (table `config`) |
| `legacy/include/pwa/Upload.php` | le contrôle du fichier envoyé, par son contenu |
| `legacy/include/pwa/Asset.php`, `Reply.php` | les réponses des points d'entrée |
| `legacy/include/pwa/Page.php`, `PageView.php`, `page.tpl` | la page d'administration |
| `legacy/include/pwa/SettingsPage.php`, `Serve*.php` | le branchement dans SuiteCRM |
| `legacy/Extension/…` | la section de l'administration, les points d'entrée, les libellés |
| `tests/` | tests du manifeste, du service worker, de l'icône, de l'envoi et de la page |

## Le module Angular

Chargé par SuiteCRM 8 au démarrage de l'interface, sans recompiler son front, il :

- pointe la page vers le manifeste de l'extension, à la place du `site.webmanifest` figé de
  SuiteCRM ;
- ajoute la couleur du thème et l'icône iOS ;
- enregistre le service worker.

## Les points d'entrée

Trois points d'entrée de SuiteCRM, ouverts sans connexion (les navigateurs les demandent
sans le cookie de session), ne servent que ceci :

| Adresse | Contenu |
|---|---|
| `legacy/index.php?entryPoint=pwaManifest` | le manifeste, construit à chaque demande |
| `legacy/index.php?entryPoint=pwaIcon` | l'icône, renvoyée vers son adresse avec version (`…&v=<version>`), gardée un an en cache |
| `legacy/index.php?entryPoint=pwaWorker` | le service worker, autorisé à couvrir tout SuiteCRM 8 (en-tête `Service-Worker-Allowed`) |

## Le service worker

Il ne garde que l'interface (`dist/` : le front compilé, les images et polices du thème),
servie depuis le cache et rafraîchie en arrière-plan. **Jamais les données du CRM** : appels
d'API, pages de l'ancienne interface et fichiers passent toujours par le réseau.

## Données en base

L'icône choisie est gardée dans la table `config` de SuiteCRM (catégorie `pwa`, découpée en
morceaux) : comprise dans la sauvegarde de la base, aucun fichier à écrire. L'icône par
défaut est dans le code (`DefaultIcon.php`).

## Installation et désinstallation

Pas de script : le Chargeur de module copie les fichiers et lance la réparation rapide. Après
une désinstallation, l'icône choisie reste dans la table `config`, et les navigateurs gardent
le service worker (voir [Dépannage](depannage.md)).

## Points d'attention

- L'extension passe le scanner du Chargeur de module : aucune fonction de fichier, aucun
  `echo` (la page passe par Smarty, les réponses par `print`).
- Le nom de l'instance s'enregistre grâce au correctif `PostedSettings` d'upstream-fixes :
  SuiteCRM 8.10 lit les réglages d'administration dans `$_GET` alors que le formulaire les
  envoie en `POST`.
