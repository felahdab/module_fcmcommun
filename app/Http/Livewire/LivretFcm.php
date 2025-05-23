<?php

namespace Modules\FcmCommun\Http\Livewire;

use Livewire\Component;
use Modules\FcmCommun\Traits\LoadsModelsDynamically;
use Filament\Notifications\Notification;
use Modules\FcmCommun\Services\EventTriggerService;
use Illuminate\Support\Facades\Log;


class LivretFcm extends Component
{

    use LoadsModelsDynamically;

    public $fcmMarinId;             // Id de FcmMarin
    public $tablePrefix;            // Prefixe de la table
    public $fcmMarinModel;     // Modele chagé dynamique
    public $parcours;


    // Fonctions Livewire
    // POur la gestion graphique de la page
    public $selectedItems = [];

    public $openSections = [];
    public $comment = '';
    public $popupData = [];
  


    





    public function mount($fcmMarinId, $tablePrefix)
    {
        $this->fcmMarinId = $fcmMarinId;
        $this->tablePrefix = $tablePrefix;

        // Charger dynamiquement MarinPArcours
        $this->fcmMarinModel = $this->getModelClass($this->tablePrefix, 'FcmMarin');
        $this->loadParcoursMarin();
       
        //dd($this->map);
    }


   


    /***
     * Vue Du parcours
     */
    public function render()
    {
        //return view('fcmcommun::livewire.livret-fcm',[
        return view('fcmcentral::filament.fcmcentral.livewire.parcours.parcours-details-validation', [
            'parcours' => $this->parcours,
            
        ]);
    }

    /**
     *
     * PARTIE ANIMATION PAGE
     */



    // Fonction pour ouvrir le popup sur le bouton valider
    public function openModal()
    {
        $this->dispatch('open-modal', id: 'comment');
    }



    // Fonctions pour ternir ou fermer les sections ouverts
    public function toggleSection($sectionId)
    {

        if (isset($this->openSections[$sectionId])) {
            unset($this->openSections[$sectionId]);
        } else {
            $this->openSections[$sectionId] = true;
        }
    }


    /**
     * Ouvrir le popup pour décocher une option.
     */
    public function decocheModal(string $type, int $id): void
    {
        $this->popupData = compact('type', 'id');
        $this->dispatch('open-modal', id: 'decocheModal');
    }

    public function infosModal(string $type, int $id, string $dateValidation, string $displayName, string $commentaire, string $libelleCourt, string $libelleLong,string $duree,): void
    {
        $this->popupData = compact('type', 'id', 'dateValidation', 'displayName', 'commentaire','libelleCourt','libelleLong','duree');
        $this->dispatch('open-modal', id: 'infosModal');
    }





    protected function loadParcoursMarin()
    {

        $fcmMarin = $this->fcmMarinModel::with('parcoursSerialises')
            ->find($this->fcmMarinId);



        if ($fcmMarin &&  $fcmMarin->parcoursSerialises->isNotEmpty()) {


            $this->parcours = $fcmMarin->parcoursSerialises->map(function ($parcours) {
                return [
                    'id' => $parcours->id,
                    'libelle_court' => $parcours->libelle_court,
                    'parcours' => json_decode($parcours->pivot->parcoursmarin, true), // Champ JSON
                    'taux_global' => $parcours->pivot->taux_global,
                    'taux_stage' => $parcours->pivot->taux_stage,
                    'taux_activite' => $parcours->pivot->taux_activite,
                ];
            })->toArray();
            $this->selectedItems['map'] = $this->generateMap($this->parcours[0]['parcours']);
            $this->selectedItems['init'] = [];
            foreach ($this->parcours as $parcoursItem){
                foreach ($parcoursItem['parcours']['fonctions'] as $fonction) {
                    
                        if (!empty($fonction['etat_valid'])) {
                            $this->selectedItems['init']['fonctions'][$fonction['id']] = true;
                            $this->selectedItems['gestion']['fonctions'][$fonction['id']] = true;
                        }
                        
                        if (isset($fonction['competences'])) {
                            foreach ($fonction['competences'] as $competence) {
                                if (!empty($competence['etat_valid'])) {
                                    $this->selectedItems['init']['competences'][$competence['id']] = true;
                                    $this->selectedItems['gestion']['competences'][$competence['id']] = true;
                                }
                                
                                if (isset($competence['savoirfaires'])) {
                                    foreach ($competence['savoirfaires'] as $savoirfaire) {
                                        if (!empty($savoirfaire['etat_valid'])) {
                                            $this->selectedItems['init']['savoirfaires'][$savoirfaire['id']] = true;
                                            $this->selectedItems['gestion']['savoirfaires'][$savoirfaire['id']] = true;
                                        }
                                        
                                        if (isset($savoirfaire['activites'])) {
                                            foreach ($savoirfaire['activites'] as $activite) {
                                                if (!empty($activite['etat_valid'])) {
                                                    $this->selectedItems['init']['activites'][$activite['id']] = true;
                                                    $this->selectedItems['gestion']['activites'][$activite['id']] = true;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
        }

        } else {
            // Gérer le cas où les données ne sont pas trouvées
            $this->parcours = [];
            $this->selectedItems = [];
        }
    }




/**
* Génère une map enfant → parent à partir de fcmMarin->parcoursSerialize.
*
* @param array $parcours
* @return array
*/
protected function generateMap(array $parcours): array
{
    // Initialisation de la structure
    $parentMap = $this->initializeParentMap();
    
    // Traitement des fonctions et de leur hiérarchie
    $this->processHierarchy($parcours, $parentMap);
    
    // Nettoyage des doublons dans les relations remontantes
    $this->cleanupDuplicateRelations($parentMap);
    
    return $parentMap;
}
 
/**
* Initialise la structure de base de la map parent-enfant
*
* @return array
*/
protected function initializeParentMap(): array
{
    $types = ['fonctions', 'competences', 'savoirfaires', 'activites'];
    $parentMap = [];
    
    foreach (['descendant', 'remontant'] as $direction) {
        $parentMap[$direction] = array_fill_keys($types, []);
    }
    
    return $parentMap;
}
 
/**
* Traite la hiérarchie des éléments du parcours
*
* @param array $parcours
* @param array &$parentMap
*/
protected function processHierarchy(array $parcours, array &$parentMap): void
{
    foreach ($parcours['fonctions'] ?? [] as $fonction) {
        $fId = $fonction['id'] ?? null;
        if (!$fId) continue;
        
        // Traitement de la fonction
        $this->processFonction($fonction, $fId, $parentMap);
    }
}
 
/**
* Traite une fonction et ses éléments enfants
*
* @param array $fonction
* @param int $fId
* @param array &$parentMap
*/
protected function processFonction(array $fonction, int $fId, array &$parentMap): void
{
    $etatValid = $fonction['etat_valid'] ?? false;
    
    // Descendant Fonction : enfants = compétences
    $parentMap['descendant']['fonctions'][$fId] = [
        'etat_valid' => $etatValid,
        'competences' => [],
    ];
    
    // Remontant Fonction : pas de parent
    $parentMap['remontant']['fonctions'][$fId] = [
        'etat_valid' => $etatValid,
    ];
    
    // Traitement des compétences liées à cette fonction
    foreach ($fonction['competences'] ?? [] as $competence) {
        $cId = $competence['id'] ?? null;
        if (!$cId) continue;
        
        $this->processCompetence($competence, $cId, $fId, $parentMap);
    }
}
 
/**
* Traite une compétence et ses éléments enfants
*
* @param array $competence
* @param int $cId
* @param int $fId
* @param array &$parentMap
*/
protected function processCompetence(array $competence, int $cId, int $fId, array &$parentMap): void
{
    $etatValid = $competence['etat_valid'] ?? false;
    
    // Descendant Compétence : enfants = savoirfaires
    $parentMap['descendant']['competences'][$cId] = [
        'etat_valid' => $etatValid,
        'savoirfaires' => [],
    ];
    
    // Remontant Compétence : parents = fonctions
    $parentMap['remontant']['competences'][$cId] = [
        'etat_valid' => $etatValid,
        'fonctions' => [$fId],
    ];
    
    // Relier fonction → compétence descendant
    $parentMap['descendant']['fonctions'][$fId]['competences'][] = $cId;
    
    // Traitement des savoir-faire liés à cette compétence
    foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
        $sId = $savoirfaire['id'] ?? null;
        if (!$sId) continue;
        
        $this->processSavoirFaire($savoirfaire, $sId, $cId, $parentMap);
    }
}
 
/**
* Traite un savoir-faire et ses éléments enfants
*
* @param array $savoirfaire
* @param int $sId
* @param int $cId
* @param array &$parentMap
*/
protected function processSavoirFaire(array $savoirfaire, int $sId, int $cId, array &$parentMap): void
{
    $etatValid = $savoirfaire['etat_valid'] ?? false;
    
    // Descendant Savoir-faire : enfants = activités
    $parentMap['descendant']['savoirfaires'][$sId] = [
        'etat_valid' => $etatValid,
        'activites' => [],
    ];
    
    // Remontant Savoir-faire : parents = compétences (possiblement plusieurs)
    if (!isset($parentMap['remontant']['savoirfaires'][$sId])) {
        $parentMap['remontant']['savoirfaires'][$sId] = [
            'etat_valid' => $etatValid,
            'competences' => [],
        ];
    }
    $parentMap['remontant']['savoirfaires'][$sId]['competences'][] = $cId;
    
    // Relier compétence → savoirfaire descendant
    $parentMap['descendant']['competences'][$cId]['savoirfaires'][] = $sId;
    
    // Traitement des activités liées à ce savoir-faire
    foreach ($savoirfaire['activites'] ?? [] as $activite) {
        $aId = $activite['id'] ?? null;
        if (!$aId) continue;
        
        $this->processActivite($activite, $aId, $sId, $parentMap);
    }
}
 
/**
* Traite une activité
*
* @param array $activite
* @param int $aId
* @param int $sId
* @param array &$parentMap
*/
protected function processActivite(array $activite, int $aId, int $sId, array &$parentMap): void
{
    $etatValid = $activite['etat_valid'] ?? false;
    
    // Descendant Activité : pas d'enfants
    $parentMap['descendant']['activites'][$aId] = [
        'etat_valid' => $etatValid,
    ];
    
    // Remontant Activité : parents = savoirfaires (possiblement plusieurs)
    if (!isset($parentMap['remontant']['activites'][$aId])) {
        $parentMap['remontant']['activites'][$aId] = [
            'etat_valid' => $etatValid,
            'savoirfaires' => [],
        ];
    }
    $parentMap['remontant']['activites'][$aId]['savoirfaires'][] = $sId;
    
    // Relier savoirfaire → activité descendant
    $parentMap['descendant']['savoirfaires'][$sId]['activites'][] = $aId;
}
 
/**
* Nettoie les doublons dans les relations remontantes
*
* @param array &$parentMap
*/
protected function cleanupDuplicateRelations(array &$parentMap): void
{
    $relationTypes = [
        'competences' => ['fonctions'],
        'savoirfaires' => ['competences'],
        'activites' => ['savoirfaires'],
    ];
    
    foreach ($relationTypes as $type => $parentTypes) {
        foreach ($parentMap['remontant'][$type] as $id => &$parents) {
            foreach ($parentTypes as $parentType) {
                if (isset($parents[$parentType])) {
                    $parents[$parentType] = array_unique($parents[$parentType]);
                }
            }
        }
    }
}






    




   
    
    
    
    public function toggleCheck(string $type, int $id)
    {
        $isChecked = isset($this->selectedItems['gestion'][$type][$id]);
        $checked = !$isChecked;

        // Met à jour l'état
        $this->setCheckedState($type, $id, $checked);
    
        // Propage la sélection aux enfants
        $this->selectDescendants($type, $id, $checked);
        
        // Mise à jour des parents
       // $this->selectAscendants($type, $id);
        //$this->updateAllParentsOf($type, $id);

     
        $this->verifyAndCorrectAllParents();
    



    }

    private function setCheckedState($type, $id, $checked)
    {
        if ($checked) {
            // Cocher l'élément
            $this->selectedItems['gestion'][$type][$id] = true;
             // Verifie si pas element est dans  tableau init pour eviter de le cocher
            if(!isset($this->selectedItems['init'][$type][$id])){
                $this->selectedItems['sauve'][$type][$id] = true;
            }
           
            // Met à jour l'état dans la map
            $this->selectedItems['map']['descendant'][$type][$id]['etat_valid'] = true;
            $this->selectedItems['map']['remontant'][$type][$id]['etat_valid'] = true;
        } else {
            // Décocher l'élément
            // Verifie si pas element est dans  tableau init pour eviter de le decocher
            if (isset($this->selectedItems['init'][$type][$id])) {
                // Si l'élément est dans init, on ne peut pas le supprimer de gestion
                // On le garde dans gestion mais on peut le retirer de sauve si besoin
                // Dans ce cas, l'élément reste visuellement coché mais n'est pas sauvegardé comme modification
            } else {
                // Si ce n'est pas dans init, on peut le supprimer de gestion
                unset($this->selectedItems['gestion'][$type][$id]);
            }
            
            // Toujours supprimer de sauve lors du décochage
            unset($this->selectedItems['sauve'][$type][$id]);

                        // Met à jour l'état dans la map seulement si ce n'est pas dans init
                        if (!isset($this->selectedItems['init'][$type][$id])) {
                            $this->selectedItems['map']['descendant'][$type][$id]['etat_valid'] = false;
                            $this->selectedItems['map']['remontant'][$type][$id]['etat_valid'] = false;
                        }
            
        }
    }


    protected function verifySavoirfaires(): void
    {
        if (!isset($this->selectedItems['map']['descendant']['savoirfaires'])) {
            return;
        }
        
        foreach ($this->selectedItems['map']['descendant']['savoirfaires'] as $savoirfaireId => $savoirfaireData) {
            // Vérifier si ce savoir-faire a des activités
            if (!isset($savoirfaireData['activites']) || empty($savoirfaireData['activites'])) {
                continue;
            }
            
            // Récupérer la liste des activités de ce savoir-faire
            $activitesIds = $savoirfaireData['activites'];
            
            // Vérifier si TOUTES les activités sont cochées dans gestion
            $allActivitesChecked = true;
            foreach ($activitesIds as $activiteId) {
                if (!isset($this->selectedItems['gestion']['activites'][$activiteId])) {
                    $allActivitesChecked = false;
                    break;
                }
            }
            
            // Mettre à jour l'état du savoir-faire
            $this->selectedItems['map']['descendant']['savoirfaires'][$savoirfaireId]['etat_valid'] = $allActivitesChecked;
            
            if (isset($this->selectedItems['map']['remontant']['savoirfaires'][$savoirfaireId])) {
                $this->selectedItems['map']['remontant']['savoirfaires'][$savoirfaireId]['etat_valid'] = $allActivitesChecked;
            }
            
            if ($allActivitesChecked) {
                // Toutes les activités sont cochées : cocher le savoir-faire
                $this->selectedItems['gestion']['savoirfaires'][$savoirfaireId] = true;
                
                // Ajouter à sauve seulement si ce n'est pas dans init
                if (!isset($this->selectedItems['init']['savoirfaires'][$savoirfaireId])) {
                    $this->selectedItems['sauve']['savoirfaires'][$savoirfaireId] = true;
                }
            } else {
                // Au moins une activité n'est pas cochée : décocher le savoir-faire
                if (!isset($this->selectedItems['init']['savoirfaires'][$savoirfaireId])) {
                    unset($this->selectedItems['gestion']['savoirfaires'][$savoirfaireId]);
                }
                unset($this->selectedItems['sauve']['savoirfaires'][$savoirfaireId]);
            }
        }
    }
    
    /**
    * Vérifie et corrige l'état des compétences en fonction de leurs savoir-faire
    */
    protected function verifyCompetences(): void
    {
        if (!isset($this->selectedItems['map']['descendant']['competences'])) {
            return;
        }
        
        foreach ($this->selectedItems['map']['descendant']['competences'] as $competenceId => $competenceData) {
            // Vérifier si cette compétence a des savoir-faire
            if (!isset($competenceData['savoirfaires']) || empty($competenceData['savoirfaires'])) {
                continue;
            }
            
            // Récupérer la liste des savoir-faire de cette compétence
            $savoirfairesIds = $competenceData['savoirfaires'];
            
            // Vérifier si TOUS les savoir-faire sont cochés dans gestion
            $allSavoirfairesChecked = true;
            foreach ($savoirfairesIds as $savoirfaireId) {
                if (!isset($this->selectedItems['gestion']['savoirfaires'][$savoirfaireId])) {
                    $allSavoirfairesChecked = false;
                    break;
                }
            }
            
            // Mettre à jour l'état de la compétence
            $this->selectedItems['map']['descendant']['competences'][$competenceId]['etat_valid'] = $allSavoirfairesChecked;
            
            if (isset($this->selectedItems['map']['remontant']['competences'][$competenceId])) {
                $this->selectedItems['map']['remontant']['competences'][$competenceId]['etat_valid'] = $allSavoirfairesChecked;
            }
            
            if ($allSavoirfairesChecked) {
                // Tous les savoir-faire sont cochés : cocher la compétence
                $this->selectedItems['gestion']['competences'][$competenceId] = true;
                
                // Ajouter à sauve seulement si ce n'est pas dans init
                if (!isset($this->selectedItems['init']['competences'][$competenceId])) {
                    $this->selectedItems['sauve']['competences'][$competenceId] = true;
                }
            } else {
                // Au moins un savoir-faire n'est pas coché : décocher la compétence
                if (!isset($this->selectedItems['init']['competences'][$competenceId])) {
                    unset($this->selectedItems['gestion']['competences'][$competenceId]);
                }
                unset($this->selectedItems['sauve']['competences'][$competenceId]);
            }
        }
    }
    
    /**
    * Vérifie et corrige l'état des fonctions en fonction de leurs compétences
    */
    protected function verifyFonctions(): void
    {
        if (!isset($this->selectedItems['map']['descendant']['fonctions'])) {
            return;
        }
        
        foreach ($this->selectedItems['map']['descendant']['fonctions'] as $fonctionId => $fonctionData) {
            // Vérifier si cette fonction a des compétences
            if (!isset($fonctionData['competences']) || empty($fonctionData['competences'])) {
                continue;
            }
            
            // Récupérer la liste des compétences de cette fonction
            $competencesIds = $fonctionData['competences'];
            
            // Vérifier si TOUTES les compétences sont cochées dans gestion
            $allCompetencesChecked = true;
            foreach ($competencesIds as $competenceId) {
                if (!isset($this->selectedItems['gestion']['competences'][$competenceId])) {
                    $allCompetencesChecked = false;
                    break;
                }
            }
            
            // Mettre à jour l'état de la fonction
            $this->selectedItems['map']['descendant']['fonctions'][$fonctionId]['etat_valid'] = $allCompetencesChecked;
            
            if (isset($this->selectedItems['map']['remontant']['fonctions'][$fonctionId])) {
                $this->selectedItems['map']['remontant']['fonctions'][$fonctionId]['etat_valid'] = $allCompetencesChecked;
            }
            
            if ($allCompetencesChecked) {
                // Toutes les compétences sont cochées : cocher la fonction
                $this->selectedItems['gestion']['fonctions'][$fonctionId] = true;
                
                // Ajouter à sauve seulement si ce n'est pas dans init
                if (!isset($this->selectedItems['init']['fonctions'][$fonctionId])) {
                    $this->selectedItems['sauve']['fonctions'][$fonctionId] = true;
                }
            } else {
                // Au moins une compétence n'est pas cochée : décocher la fonction
                if (!isset($this->selectedItems['init']['fonctions'][$fonctionId])) {
                    unset($this->selectedItems['gestion']['fonctions'][$fonctionId]);
                }
                unset($this->selectedItems['sauve']['fonctions'][$fonctionId]);
            }
        }
    }
    
    /**
    * Vérifie et corrige l'état de tous les parents (à appeler dans toggleCheck)
    */
    protected function verifyAndCorrectAllParents(): void
    {
        // Ordre important : du bas vers le haut de la hiérarchie
        $this->verifySavoirfaires();  // D'abord les savoir-faire (basés sur les activités)
        $this->verifyCompetences();   // Puis les compétences (basées sur les savoir-faire)
        $this->verifyFonctions();     // Enfin les fonctions (basées sur les compétences)
    }
    



    

   

    protected function selectDescendants(string $type, int $id, bool $etat)
    {
        $childrenTypes = [
            'fonctions' => 'competences',
            'competences' => 'savoirfaires',
            'savoirfaires' => 'activites',
            'activites' => null,
        ];

        $childrenType = $childrenTypes[$type] ?? null;
        if (!$childrenType || !isset($this->selectedItems['map']['descendant'][$type][$id][$childrenType])) return;

        foreach ($this->selectedItems['map']['descendant'][$type][$id][$childrenType] as $childId) {
            // Met à jour l'état dans la map
            $this->selectedItems['map']['descendant'][$childrenType][$childId]['etat_valid'] = $etat;
            $this->selectedItems['map']['remontant'][$childrenType][$childId]['etat_valid'] = $etat;

            if ($etat) {
                // Ajoute à la sélection
                $this->selectedItems['gestion'][$childrenType][$childId] = true;
                
                // Ajouter à sauve seulement si ce n'est pas dans init
                if (!isset($this->selectedItems['init'][$childrenType][$childId])) {
                    $this->selectedItems['sauve'][$childrenType][$childId] = true;
                }
            } else {
                // Retire de la sélection selon les règles
                if (!isset($this->selectedItems['init'][$childrenType][$childId])) {
                    unset($this->selectedItems['gestion'][$childrenType][$childId]);
                }
                unset($this->selectedItems['sauve'][$childrenType][$childId]);
            }


            // Propage aux descendants
            $this->selectDescendants($childrenType, $childId, $etat);
        }
    }

    


    




   




/**
     *
     * PARTIE SAUVEGARDE A REFAIRE AVEC EVENT
     */


     public function saveForm()
     {
         // Recalculer les taux globaux pour tous les parcours
        //  foreach ($this->parcours as $parcoursItem) {
        //      // $this->calculerTauxGlobal($parcoursItem['id']);
        //  }
 
         // Charger le modèle MarinParcours
         // $marinParcoursClass = $this->getModelClass($this->tablePrefix, 'MarinParcours');
         // $record = $marinParcoursClass::with('parcoursSerialises')->find($this->fcmMarinId);
 
         $record  = $this->fcmMarinModel::with('parcoursSerialises')
             ->find($this->fcmMarinId);
 
         if (!$record) {
             Notification::make()
                 ->title('Erreur')
                 ->body('Impossible de trouver le marin ou ses parcours.')
                 ->danger()
                 ->send();
             return;
         }
 
       
         // Déclencher l'événement pour sauvegarder les données
         $data = [
             'commentaire' => $this->comment,
             'selectedItems' => $this->selectedItems,
             'marinUuid' => $record->marin->uuid,
             'eventName' => 'valid',
         ];
         //dd($record,$data);
         EventTriggerService::triggerEvent($data, $record);
 
         // Réinitialiser les données après la sauvegarde
         $this->selectedItems['sauve'] = [];
         $this->comment = [];
 
         // Fermer le modal
         $this->dispatch('close-modal', ['id' => 'comment']);

         // Rafraichir le Livewire
         $this->dispatch('refresh-livret-fcm');
         


              // 

        

        
     }



   







    /**
     *  Partie Taux et cochage
     *
     */



    public function calculerTauxGlobal($parcoursId)
    {
        // Trouver le parcours correspondant
        $parcoursIndex = array_search($parcoursId, array_column($this->parcours, 'id'));
        $parcours = $this->parcours[$parcoursIndex];

        // Totaux
        $totalFonctions = count($parcours['parcours']['fonctions'] ?? []);
        $totalCompetences = 0;
        $totalSavoirFaires = 0;
        $totalActivites = 0;

        // Calculer les totaux
        foreach ($parcours['parcours']['fonctions'] as $fonction) {
            $totalCompetences += count($fonction['competences'] ?? []);
            foreach ($fonction['competences'] as $competence) {
                $totalSavoirFaires += count($competence['savoirfaires'] ?? []);
                foreach ($competence['savoirfaires'] as $savoirFaire) {
                    $totalActivites += count($savoirFaire['activites'] ?? []);
                }
            }
        }

        $totalElements = $totalFonctions + $totalCompetences + $totalSavoirFaires + $totalActivites;

        // Éléments cochés
        $checkedFonctions = count($this->selectedItems[$parcoursId]['fonctions'] ?? []);
        $checkedCompetences = 0;
        $checkedSavoirFaires = 0;
        $checkedActivites = 0;

        // Parcourir les fonctions cochées
        foreach ($this->selectedItems[$parcoursId]['fonctions'] ?? [] as $fonctionId => $isChecked) {
            if ($isChecked) {
                $checkedFonctions++;
            }
        }

        // Parcourir les compétences cochées
        foreach ($this->selectedItems[$parcoursId]['competences'] ?? [] as $competenceId => $isChecked) {
            if ($isChecked) {
                $checkedCompetences++;
            }
        }

        // Parcourir les savoir-faire cochés
        foreach ($this->selectedItems[$parcoursId]['savoirFaires'] ?? [] as $savoirFaireId => $isChecked) {
            if ($isChecked) {
                $checkedSavoirFaires++;
            }
        }

        // Parcourir les activités cochées
        foreach ($this->selectedItems[$parcoursId]['activites'] ?? [] as $activiteId => $isChecked) {
            if ($isChecked) {
                $checkedActivites++;
            }
        }

        $checkedElements = $checkedFonctions + $checkedCompetences + $checkedSavoirFaires + $checkedActivites;

        // Debug : Afficher les totaux et les éléments cochés
        // dd([
        //     'parcoursId' => $parcoursId,
        //     'totalFonctions' => $totalFonctions,
        //     'totalCompetences' => $totalCompetences,
        //     'totalSavoirFaires' => $totalSavoirFaires,
        //     'totalActivites' => $totalActivites,
        //     'totalElements' => $totalElements,
        //     'selectedItems' => $this->selectedItems[$parcoursId] ?? [],
        //     'checkedFonctions' => $checkedFonctions,
        //     'checkedCompetences' => $checkedCompetences,
        //     'checkedSavoirFaires' => $checkedSavoirFaires,
        //     'checkedActivites' => $checkedActivites,
        //     'checkedElements' => $checkedElements,
        // ]);

        // Calcul du taux global (règle de trois)
        $tauxGlobal = $totalElements > 0 ? ($checkedElements / $totalElements) * 100 : 0;

        // Mettre à jour le taux global dans le parcours
        $this->parcours[$parcoursIndex]['taux_global'] = round($tauxGlobal, 2);
    }
}
