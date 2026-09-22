## 0.2.6 (September 22, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.5 –> 0.2.6
- Implement art #967307 : nouvelle migration pour transformer le champs libelle des notes en text (pour avoir plus de place)

## 0.2.5 (September 03, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.4 –> 0.2.5
- Implements art #927866 : Modification pour S3 Private , changement url
- Correction callback sur l evenement creating du ParcoursSerialise: l uuid ne doit etre cree que s'il n'existe pas deja.
- Correction rollback de la migration updateFcmMarinEtat
- Correction tipo dans la migration de la table fcm_marins
- Implements art #927866 : Ajout  Icon PDF ajout Method pour event LIsteners ajout/supp modification methodes pour ajouter et supprimer piece jointe dans le PArcours
- Implements art #927866 : Refactorisation de LivretFcmLight Refactorisation de item.blade.php
- Implements art #927866 : modification pour rajout Upload dans parcours
- Implements art #805355 : suppression ligne mentor_id dans la migration

## 0.2.4 (March 27, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.3 –> 0.2.4
- Implements art #722833 : Modification de getHeures  private en public pour les widgets FcmCentral
- Implements art #722833 : modifier calcul taux avec horaires objectifs
- Implements art #722821 : modification dans les vues pour pouvoir antidater une validation

## 0.2.3 (March 24, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.2 –> 0.2.3
- Implements art #722480 : bug modal initiation alpine date qui plante l'ouverture des autres modals

## 0.2.2 (March 20, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.1 –> 0.2.2
- Implements art #717730 : Ajout input date pour antidater les validations
- Implements art #717730 : Autorisations implicites pour Marin et Mentor,  modification et rajout methode canDevalider
- Implements art #717301 : Modifcation vue LivrectFCM bug v4 et Seeder

## 0.2.1 (March 10, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.2.0 –> 0.2.1
- Suppression des tests Mentor de ce module. Déplacement dans FcmCentral.
- Ajustement de tests anciens

## 0.2.0 (February 19, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.1.2 –> 0.2.0
- Ajustements pour Filament v4 (donc skeletor v4.0.0+)

## 0.1.2 (February 16, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.1.1 –> 0.1.2
- Implements art #696979 : Correctif Bug Apostrophe ok dans item blade parcours.
- Rajout Permission pour refuser une proposition dans LivretFCm

## 0.1.1 (February 13, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.1.0 –> 0.1.1
- Implements art #685754 : modifications diverses sur les IHM et les permissions
- Retrait de la référence à FcmCentral dans le service PDF. Ce service travaille sur un objet de type Marin qui peut être soit un FcmCentral\Models\Marin, soit un FcmUnite\Models\Marin.
- Ajout d'un htmlspecialchars pour protéger les chaines de caractères provenant de la définition des parcours dans les composants alpinejs du livret de FCM.
- La migration du parcours d'un marin lors du figement d'une nouvelle version doit prendre en compte les parties du parcours qui font l'objet d'une proposition. Sinon, on perd des données.
- L'état de proposition doit être inclus dès l'initialisation du parcours du marin à l'attribution d'un parcours sérialisé.
- Implements art #683344 : Modification parcours pour mettre des UUID a la place ID Suppression migration Documentation
- Implements art #677000 Correction Bug  Serialisation Parcours Marin
- Retrait du panneau Filament du module.
- Rationalisation de la gestion des permissions dans le livret. Toutes les vérifications, front et back, sont faites dans les méthodes PHP du composant Livewire. Lorsque le livret est consulté par le marin, il a forcément le droit de proposer des éléments à la validation de son mentor.
- Retrait de dépendances descendantes vers FcmCentral. TODO: il reste des dépendances descendantes vers FcmCentral qui doivent être supprimées.
- Implements art #668651 : Correction et refonte du livret Marin parcours en PDF
- Implements art #668651 : Mise a Jour migrations Jalons, rajout Jalons

## 0.1.0 (January 13, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.10 –> 0.1.0
- Implements art #665468 : Suppression des Helpers des Test  Unit
- Implements art #663213 : Modification droits dans item (bug)
- Implements art #658135 : Travaux modication BCF
- Le livret de FCM a maintenant comme paramètres la classe Marin, la classe UserGeneratedEvent à utiliser, ainsi que le prefixe (fcmcentral ou fcmunite) à utiliser pour construire les permissions utilisées. Ainsi, le composant peut être utilisé aussi bien depuis FcmCentral que FcmUnite à condition d'adapter ces paramètres.
- Modifications élémentaires pour régler les discordances PSR-4
- Modification migration pour eviter dépendances dans le mauvais sens vers FcmUnite et FcmCentral.
- Bug Modal Infos EAD S
- Correction bug affichage : SF identique mais type different, objectifs existant mais non affichés
- Correction bug Notes Etats Marin
- Refonte MarinResource
- Bug Note
- PPA version 2
- Droits FCM Light
- Modif nouveau parcours PPA
- Correction bug affichage filtres
- Misa a jour LivretFcm, moteur de recherche , filtre
- Modification Visuel Livret pour suggestion marin en attente
- Remise en place script Alpine Parents / Enfants  Parcours avec OutilsMArinParcoursService

## 0.0.10 (August 25, 2025)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.9 –> 0.0.10
- Adaptation du livret de FCM
- WIP sur nouvelle version plus légère et rapide du livret de FCM
- Ajustement OutilsMarinParcoursService pour ne travailler que sur les données sérialisées du parcours, sans référence aux classes du module FcmCentral.
- Nettoyage MarinParcours
- Merge remote-tracking branch 'origin/job_maj_ppa_marinfcm_sza' into fldb-nouveau-livret
- Livret FCM ALpineJS v3
- Livret FCM AlpineJs V2
- Debut Modification Parcours bouton AlpineJS
- wip suite
- Merge remote-tracking branch 'origin/julien-pdf' into job_maj_ppa_marinfcm_sza
- Livret FCM : Event SavoirFaire
- wip job serialisation
- complement outils marin parcours
- debut service outils marin parcours
- PDF Livret v1
- sauv idees

## 0.0.9 (July 25, 2025)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.8 –> 0.0.9
- Retrait des classes de génération des events devenues obsolètes
- TRetrait des anciens tests EventTriggerService

## 0.0.8 (July 25, 2025)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.7 –> 0.0.8
- Ajustement livret pour émtettre les évènements de validation des éléments du parcours déjà proposés par le marin.

## 0.0.7 (July 25, 2025)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.6 –> 0.0.7
- Merge branch 'julien-recalcul-etat-parcours-apres-evenement-metier'
- Commit : Verification Etats Parents si Enfants tous cochés

## 0.0.6 (July 23, 2025)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.5 –> 0.0.6
- Restauration du fonctionnement du livret pour générer une série d'évènements uniques plutôt qu'un évènement groupé.
- Merge remote-tracking branch 'origin/julien-simplification-events' into fldb-simplification-events
- Commit  LivretFCM MAJ + Validation
- Merge branch 'master' into fldb-simplification-events
- Retrait de la référence au NavigationHelper dans le panneau FcmCommun.
- Suppression du NavigationFilamentHelper, remplacé par la déclaration des accès rapides.
- Ajustement mise en forme pour lisibilité du code.
- Modifications diverses du livret pour travailler sur les uuid et pas les id.
- Début de simplification des évènements FCM. Reste notamment à traiter le cas du livret de transformation.
- Réintégration modification de la migration StoredEvent dans la migration initiale.
- Implements art #480061 : Amelioration Test Unitaire pour EventTrigger
- Implements art #480061 : Modification des Test Unitaire pour EventTrigger
- Implements art #480061 : Debut des Test Unitaires
- Implements art #480061 : Optimisation Livret FCM
- Implements art #480061 : Livret FCM
- Implements art #480061 : Livret FCM  Proposition Restore Reboot
- Implements art  #480061 : Livret FCM v3 (proposition)
- implements art #480061  : Livret FCM v2 (Validation, Annulation)
- Implements art #480061 : Mise a jour Livret FCM Gestion du parcours + sauvegarde event Difficulte sur les many to many avec ascendants et descendants mais c'est ok Toute la logique est mis dans le Livewire pour eviter d'inserer du jquery Soucis sur FcmCentral_stored car il cherche le uuid du Marin_Parcours Rafraichissement ne fonctionne pas apres mise a jour doit faire Annulation cas unique et Proposition && Annul_Proposition (juste event a construire) etude a venir sur la presentation du parcours Rajout champ supplementaire sur le Parcours Serialise

## 0.0.5 (May 02, 2025)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.4 –> 0.0.5
- Correction FcmMarinListener pour tenir compte de l'état courant du suivi_en_fcm. Simplification du ParcoursSerialiseMarinListener: normalement, on ne devrait plus travailler avec les objets FcmMarin, mais seulement avec des Marin. En outre, modification pour éviter de pouvoir rajouter plusieurs parcours à un Marin. Modification modèle Marin pour créér la ligne dans la table FcmMarin lorsque le Marin passe en suivi FCM.
- Merge remote-tracking branch 'origin/master'
- Implements art #463585 Creation Event Sauve
- Merge remote-tracking branch 'origin/fldb-temp'
- Revert "???"
- Implements art #463585 Creation Event Sauve
- ???

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

