# Utilisation

## Ce qui change

Le CRM peut être installé comme une application. Une fois installé, il a son icône sur
l'écran d'accueil ou dans le menu de l'ordinateur, s'ouvre en plein écran sans barre
d'adresse, et affiche une page « Hors connexion » quand le réseau manque.

## Installer sur un téléphone ou un ordinateur

Se connecter au CRM, **recharger la page une fois** (le navigateur enregistre alors le
service worker), puis :

| Appareil | Navigateur | Où |
|---|---|---|
| Android | Chrome | menu **⋮** › **Ajouter à l'écran d'accueil** › **Installer** (ou le bandeau « Installer l'application » en bas de l'écran) |
| Android (Samsung) | Samsung Internet | icône d'installation (flèche vers le bas) dans la barre d'adresse, ou menu **≡** › **Ajouter la page à** › **Écran d'accueil** |
| iPhone, iPad | Safari | **Partager** › **Sur l'écran d'accueil** |
| Ordinateur | Chrome, Edge | icône **Installer** (écran avec une flèche) à droite de la barre d'adresse |

Vérifié sur un Samsung Galaxy A55 (Chrome).

## Réglages

- **Nom** : le nom système de l'instance (**Administration › Paramètres système**,
  « SuiteCRM » par défaut), repris aussitôt. Sous l'icône, « CRM » s'il dépasse
  12 caractères.
- **Icône** : **Administration › Application installable › Nom et icône de
  l'application** : une image PNG carrée de 512 × 512 px au moins (1 Mo au plus), puis
  **Enregistrer**. **Revenir à l'icône par défaut** rend l'icône neutre « CRM ». Réservé aux
  administrateurs.

Une application déjà installée reprend un nouveau nom ou une nouvelle icône à sa prochaine
mise à jour : le navigateur relit le manifeste à l'ouverture, environ une fois par jour,
parfois avec une confirmation. Pour les voir tout de suite, désinstaller puis réinstaller
l'application.

## Données de démo

Aucune.

## Limites

Pas de mode hors ligne : l'application garde son interface en cache, jamais les données du
CRM. Sans réseau, elle affiche la page « Hors connexion ».
