## 0.0.4 (April 18, 2025)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.3 –> 0.0.4
- Suites aux modifications dans le modèle FcmCommun\Models\Marin, cette Ressource doit être revue. Eventuellement supprimée.
- Désactivation du menu "Modules". L'implémentation doit être revue pour éviter les dépendances descendantes.
- Suppression ancienne page de test devenue inutile
- Retrait des méthodes inutiles. Création de l'attribut suiviEnFcm et du scope associé.
- Standardisation du marquant suivi _en_fcm. Scope local associé. Test unitaires associés.
- Création de la commande de mise à jour des cohortes depuis une instance distante. Insription de cette tache dans les taches récurrentes quotidiennes.

## 0.0.3 (April 16, 2025)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.2 –> 0.0.3
- Merge branch 'tuleap-419581-refonte-tables-fcmcentral-fcmunite-rh-modification-filament-fcmcentral-fcmunite'
- Ajustement PSR-4
- implements art #419581
- Implements art #419581 : Update et ParcoursSerialise
- Merge remote-tracking branch 'origin/tuleap-419581-refonte-tables-fcmcentral-fcmunite-rh-modification-filament-fcmcentral-fcmunite'
- Implements art #419581 : Mise a jour Events Listener pour Fcm Suivi Marin Creation des Events Listeners pour l'enregistrement des marins en FCM Notification avec Filament , logs ...
- Refonte des Tables Migration + Seeders ligne commande : php artisan julien:refresh-db Gestion  des Parcours, page Parcours View Travaux sur plusieurs modules FcmCommun, FcmCentral, FcmUnite, RH
- Suppression des widgets inutiles du dashboard
- Merge branch 'master' of /home/coder/project3/Modules/FcmCommun
- Ajout du champs data aux objets MarinParcours
- On introduit les éléments de Saloon dans FcmCentral puisque ce module va devoir envoyer ses evenements par API
- wip
- wip
- wip
- wip
- wip
- Nettoyage
- Nettoyage
- Ajustements
- La migration qui cree la table user_parcours prend desormais en parametre la table des utilisateurs ou marins.
- Conversion vers un livret de transformation en full livewire.

## 0.0.2 (September 09, 2024)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.1 –> 0.0.2
- ajout bump-version

## 0.0.1 (September 09, 2024)
- Created VERSION, Created CHANGELOG.md, Bumped to 0.0.1
- wip
- Modification du livret de transformation et du service ParcoursService pour permettre l'emploi de classes dynamiques dans les modules FcmCentral et FcmUnite.
- Adaptations pour gérer plusieurs parcours simultanément
- Transmission du commentaire au service
- wip events
- Creation d'un modele User dans FcmCommun.
- Make the attribution of the id of UserParcours automatic.
- wip userparcours
- Conversion du modele UserParcours en modele commun.
- Creation de 2 migrations comunes supplémentaires et du modele ParcoursSerialise
- Retrait de commentaire superflu.
- Fait en sorte que le bouton de mise a jour du livret disparaisse quand l'utilisateur n'a pas le droit.
- Ajout de la verification de l'autorisation de mise à jour du livret et du refresh.
- Transformation de la migration des stored event en une classe destinee a etre utilisee par les autres modules.
- Ajout de l'evenement refresh au livret de FCM pour provoquer une mise à jour.
- Introduction des actions Filament dans le composant Livewire. Utilisation des event pour declancher des actions du composant Vue.
- Creation d'une ressource User, avec une page Livret de Transformation, pour demontrer la possibilite d'intégrer le composant LiveWire contenant le composant Vue dans une page Filament.
- Ajustement du composant LiveWire du livret de transformation.
- Ajout du state destine au tree view vuejs pour afficher et valider le parcours.
- Il faut faire référence aux utilisateurs par leur uuid.
- wip mise en forme du livret
- wip sur validation et devalidation d'elements du parcours
- wip
- Adaptation des DTO au nouveau modele de donnees FCM
- wip
- Simplification du code.
- Première version fonctionnelle de vue3-treeview qui charge ses données depuis Livewire.
- npm install vue3-treeview
- Integration d'un composant  Vue pour la présentation du livret de transformation dans un composant Livewire.
- wip
- Ajustement des DTO pour serialisation et deserialisation des parcours.
- Ajout du slug du module comme prefixe de table en base
- Ajout du slug du module dans les url des routes api
- Reprise des travaux exploratoires
- Mise a jour de composer.json et de la configuration du module
- Creation d'un panel Filament dans le module
- Ajout du prefixe skeletor aux routes du module.
- Initial commit after creation

