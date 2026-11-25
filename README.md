# Application installable (`pwa`)

Le CRM **s'installe comme une application** (PWA) sur téléphone et sur ordinateur, avec le
nom de l'instance et son icône. Il s'ouvre alors dans sa propre fenêtre, sans barre
d'adresse, et affiche une page « Hors connexion » quand le réseau manque.

| Fiche | |
|---|---|
| Version de SuiteCRM | 8.10 (`>=8.10 <9`) |
| Types | `ui` |
| Dépend de | [upstream-fixes](https://github.com/david-grosskost/suitecrm-ext-upstream-fixes) 0.2.1 ou plus (installée d'office), pour l'enregistrement du nom de l'instance |
| Installée d'office | non |

## Installer

Sur une instance, par **Administration → Chargeur de module** : l'extension y figure dans
la liste des paquets disponibles, cliquer sur **Installer**, puis recharger l'interface
(Ctrl+F5). Depuis le terminal, dans suitecrm-core :

```bash
make extensions@install SLUGS="pwa"
```

La désinstallation se fait au même endroit. Le site doit être servi en HTTPS.

## Documentation

| Page | Contenu |
|---|---|
| [Utilisation](docs/utilisation.md) | installer l'application sur un téléphone ou un ordinateur, nom et icône |
| [Fonctionnement](docs/fonctionnement.md) | module Angular, points d'entrée, service worker, icône en base |
| [Développement](docs/developpement.md) | modifier le module ou le service worker, vérifier, tester en local |
| [Dépannage](docs/depannage.md) | « Ajouter un raccourci » au lieu d'« Installer », service worker d'une instance disparue |

Les commandes, la CI et le format des extensions sont décrits dans la documentation de
[suitecrm-devkit](https://github.com/david-grosskost/suitecrm-devkit#documentation).
