# Dépannage

## « Ajouter un raccourci » au lieu d'« Installer »

Le navigateur n'a pas encore vu le manifeste ou le service worker :

1. recharger la page une fois connecté ;
2. vérifier que l'extension est installée (**Administration → Chargeur de module**) ;
3. vérifier que le site est servi en HTTPS.

## Le nom ou l'icône ne changent pas sur une application installée

Le navigateur relit le manifeste à l'ouverture, environ une fois par jour. Pour voir le
changement tout de suite : désinstaller l'application, puis la réinstaller.

## Un service worker survit à son instance

C'est le navigateur qui garde le service worker, pas le serveur. Il reste donc en place
quand l'instance change sous lui : extension désinstallée, instance reconstruite de zéro,
base effacée. Le navigateur continue de servir l'interface depuis son cache et d'appeler un
point d'entrée qui n'existe peut-être plus.

Ça se manifeste par des comportements qui n'ont aucun sens : des écrans d'une version
précédente, des erreurs sur des adresses qui répondent correctement quand on les ouvre à la
main, ou un symptôme qu'on croit avoir corrigé et qui revient.

**Après avoir détruit ou reconstruit une instance**, vider le site dans le navigateur :
F12 → **Application** → **Storage** → *Clear site data*. Fermer ensuite **tous** les
onglets du site. Un simple F5 ne suffit pas : un service worker continue de contrôler les
pages déjà ouvertes jusqu'à ce que la dernière soit fermée, même après sa désinscription.

Ça vaut surtout pour une machine où l'on crée des démos à la chaîne sur la même adresse :
le service worker de la démo précédente est encore là quand la suivante démarre.
