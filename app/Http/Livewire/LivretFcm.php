<?php

namespace Modules\FcmCommun\Http\Livewire;

use Carbon\Carbon;

use Illuminate\Support\Facades\Log;

use Livewire\Component;

use Filament\Notifications\Notification;

use Modules\FcmCommun\Traits\LoadsModelsDynamically;
use Modules\FcmCommun\Services\EventTriggerService;
use Modules\FcmCommun\Services\HierarchyMapGeneratorService;
use Modules\FcmCommun\Services\ParcoursValidationService;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

class LivretFcm extends Component
{

    use LoadsModelsDynamically;

    public $fcmMarinId;
    public $parcoursId;
    public $tablePrefix;
    public $fcmMarinModel;
    public $fcmMarin;
    public $parcours;
    public $parcoursMarin; 

    public $eventClass;

    // État de l'interface
    public $selectedItems = [];
    public $openSections = [];
    public $comment = '';
    public $popupData = [];
    public $selectedRestoreEvent=null;
    public $restore = [];



    public function mount($fcmMarinId, $tablePrefix, $parcoursMarin, $eventClass)
    {
        $startTime = microtime(true);
        Log::info("=== MOUNT LIVEWIRE OPTIMISÉ ===", ['fcmMarinId' => $fcmMarinId]);
 
        $this->fcmMarinId = $fcmMarinId;
        $this->tablePrefix = $tablePrefix;
        $this->parcoursMarin = $parcoursMarin;
        $this->eventClass = $eventClass;
        
        // ✅ VÉRIFIER QUE LES DONNÉES SONT PRÉSENTES
        if (!$parcoursMarin || !isset($parcoursMarin['fcmMarin'])) {
            Log::error("Données parcoursMarin manquantes pour marin: {$fcmMarinId}");
            $this->parcours = [];
            $this->selectedItems = [];
            return;
        }
 
        // ✅ CHARGER DIRECTEMENT DEPUIS LES DONNÉES PRÉ-CHARGÉES
        $this->fcmMarin = $parcoursMarin['fcmMarin'];
        
        if ($parcoursMarin['parcoursSerialises']->isNotEmpty()) {
            $this->parcours = $this->formatParcours($parcoursMarin['parcoursSerialises']);
            $this->initializeSelectedItems();
        } else {
            $this->parcours = [];
            $this->selectedItems = [];
            Log::info("Aucun parcours sérialisé trouvé pour marin: {$fcmMarinId}");
        }
 
        // ✅ CHARGER RESTORE DATA
        $this->loadRestoreData();
 
        $mountTime = round((microtime(true) - $startTime) * 1000, 2);
        Log::info("Mount ultra-rapide terminé en: {$mountTime}ms");
    }
 
    public function render()
    {
        return view('fcmcommun::livewire.parcours.parcours-details-validation', [
            'parcours' => $this->parcours,
            'fcmMarin' => $this->fcmMarin,
            'restore' => $this->restore,
        ]);
    }


 
 

    // ========================================
    // MÉTHODES PRINCIPALES SIMPLIFIÉES
    // ========================================

    public function toggleCheck(string $type, string $uuid)
    {
        $startTime = microtime(true);
        $memoryStart = memory_get_usage(true);
        
        Log::info("=== DIAGNOSTIC DÉLAI LIVEWIRE ===", [
            'method' => 'toggleCheck',
            'type' => $type,
            'uuid' => $uuid,
            'memory_start' => $memoryStart,
            'timestamp' => now()->toDateTimeString()
        ]);
        

           // 1. Toggle immédiat
        $service = new ParcoursValidationService($this->selectedItems, $this->selectedItems['map']);
          $this->selectedItems = $service->toggleValidation($type, $uuid);
        
    // // 2. Pas de re-render
    // $this->skipRender();


    // // Envoyer  selectedItems json pour javascript
    // $selectedItemsJson = json_encode($this->selectedItems['gestion']);

    // // 3. Passer par javascript pour refaire affichage page
   
    // $this->js("
    //     document.dispatchEvent(new CustomEvent('parcours-update', {
    //         detail: {$selectedItemsJson}
    //     }));
    // ");

    
  

      /////

     
      
     
          
          $processingTime = round((microtime(true) - $startTime) * 1000, 2);
          $memoryEnd = memory_get_usage(true);
          
          Log::info("=== RÉSULTAT DIAGNOSTIC ===", [
              'processing_time' => $processingTime . 'ms',
              'memory_used' => ($memoryEnd - $memoryStart) . ' bytes',
              'memory_peak' => memory_get_peak_usage(true),
              'timestamp' => now()->toDateTimeString()
              
          ]);
          
      
  

      //////
    
       

     
    }

    

    // ========================================
    // MODALS
    // ========================================

    // Modal Annulation
    public function annulModal(string $type, string $uuid): void
    {
        $this->popupData = compact('type', 'uuid');
        $this->dispatch('open-modal', id: 'annul');
        
        $service = new ParcoursValidationService($this->selectedItems, $this->selectedItems['map']);
        $this->selectedItems = $service->toggleAnnulation($type, $uuid);
    }

    // Modal Proposition
    public function proposModal(string $type, string $uuid): void
    {
        $this->popupData = compact('type', 'uuid');
        $this->dispatch('open-modal', id: 'propos');
        
        $service = new ParcoursValidationService($this->selectedItems, $this->selectedItems['map']);
        $this->selectedItems = $service->toggleProposition($type, $uuid);
    }

    // Modal Informations
    public function infosModal(string $type, int $uuid, string $dateValidation, string $displayName, string $commentaire, string $libelleCourt, string $libelleLong, string $duree,): void
    {
        $this->popupData = compact('type', 'id', 'uuid', 'dateValidation', 'displayName', 'commentaire', 'libelleCourt', 'libelleLong', 'duree');
        $this->dispatch('open-modal', id: 'infosModal');
    }

    // Fonction pour ouvrir le popup sur le bouton valider
    public function sauveModal()
    {
        $this->dispatch('open-modal', id: 'sauve');
    }


    // ========================================
    // MÉTHODES DE CHARGEMENT DES DONNÉES
    // ========================================

    protected function loadParcoursMarin($parcoursId = '')
{
   
   
    $startTime = microtime(true);
    
    // ✅ SÉLECTION DES CHAMPS SPÉCIFIQUES
    $fcmMarin = $this->fcmMarinModel::select([
        'id',
        'rh_marin_id',
        // Ajoutez d'autres champs si nécessaires pour votre logique
    ])
    ->with([
        'parcoursSerialises:id,libelle_court',
        //'parcoursSerialises.pivot:fcmmarin_id,parcoursserialise_id,parcoursmarin,taux_global,taux_stage,taux_activite',
        'marin:id,uuid,nom,prenom'
    ])
    ->find($this->fcmMarinId);

 
    $this->fcmMarin = $fcmMarin;
    
    if ($fcmMarin && $fcmMarin->parcoursSerialises->isNotEmpty()) {
        $this->parcours = $this->formatParcours($fcmMarin->parcoursSerialises);
        $this->initializeSelectedItems();
    } else {
        $this->parcours = [];
        $this->selectedItems = [];
        Log::info("No parcours found for marin: {$this->fcmMarinId}");
    }
    
    $loadTime = round((microtime(true) - $startTime) * 1000, 2);
    Log::info("loadParcoursMarin optimized: {$loadTime}ms", [
        'fcmMarinId' => $this->fcmMarinId,
        'parcours_count' => count($this->parcours)
    ]);
}


    protected function formatParcours($parcoursSerialises)
    {
        return $parcoursSerialises->map(function ($parcours) {
            return [
                'id' => $parcours->id,
                'uuid' => $parcours->uuid,
                'libelle_court' => $parcours->libelle_court,
                'parcours' => json_decode($parcours->pivot->parcoursmarin, true),
                'taux_global' => $parcours->pivot->taux_global,
                'taux_stage' => $parcours->pivot->taux_stage,
                'taux_activite' => $parcours->pivot->taux_activite,
            ];
        })->toArray();
    }

    // ========================================
    // INIT DES DONNÉES
    // ========================================

    protected function initializeSelectedItems()
    {
        $generator = new HierarchyMapGeneratorService();
        $this->selectedItems['map'] = $generator->generate($this->parcours[0]['parcours']);
        $this->selectedItems['init'] = [];
        $this->selectedItems['gestion'] = [];
        
        $this->populateInitialItems();
    }

    protected function populateInitialItems()
    {
        foreach ($this->parcours as $parcoursItem) {
            foreach ($parcoursItem['parcours']['fonctions'] as $fonction) {
                $this->processInitialElement('fonctions', $fonction);
                
                if (isset($fonction['competences'])) {
                    foreach ($fonction['competences'] as $competence) {
                        $this->processInitialElement('competences', $competence);
                        
                        if (isset($competence['savoirfaires'])) {
                            foreach ($competence['savoirfaires'] as $savoirfaire) {
                                $this->processInitialElement('savoirfaires', $savoirfaire);
                                
                                if (isset($savoirfaire['activites'])) {
                                    foreach ($savoirfaire['activites'] as $activite) {
                                        $this->processInitialElement('activites', $activite);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    protected function processInitialElement(string $type, array $element)
    {
        if (!empty($element['etat_valid'])) {
            $this->selectedItems['init'][$type][$element['uuid']] = true;
            $this->selectedItems['gestion'][$type][$element['uuid']] = true;
        }

        // Pour la  (Validation||Annulation)Proposition Mentor||User
        if (!empty($element['date_proposition'])){
        
            $this->selectedItems['aValiderPropos'][$type][$element['uuid']] = [
                'date_proposition' =>  $element['date_proposition'],
                'commentaire_proposition' => $element['commentaire_proposition'],
                'libelle_court' => $element['libelle_court'] ,
                'libelle_long' => $element['libelle_long'] ,
            ];      
        }
        
    }



    // ========================================
    // RESTORE DATA
    // ========================================

    protected function loadRestoreData()
    {
        //$fcmMarin = $this->fcmMarinModel::with('parcoursSerialises')->find($this->fcmMarinId);
        $fcmMarin = $this->fcmMarin;
        $storeEvents = $this->getModelClass($this->tablePrefix, 'StoredEvent');
        
        $this->restore = $storeEvents::where('object_uuid', $fcmMarin->marin->uuid)
            //->where('object_uuid', $fcmMarin->parcoursSerialises[0]->uuid)
            ->whereIn('event_type', [
                'valid_elmt_marin_parcours',
                'annul_elmt_marin_parcours',
                //'propos_elmt_marin_parcours',
            ])
            ->orderBy('created_at','DESC')
            ->get();
          
    }

    public function restoreFromEvent()
    {
        if (!$this->selectedRestoreEvent){
            return;
        }

        //$selectedEvent = $this->restore->firstWhere('id',$this->selectedRestoreEvent);
        $storeEvents = $this->getModelClass($this->tablePrefix, 'StoredEvent');
        $selectedEvent =  $storeEvents::find($this->selectedRestoreEvent);

        if($selectedEvent){
           //$this->selectedItems= $selectedEvent->detail;
           switch($selectedEvent->event_type){
            case 'valid_elmt_marin_parcours' : $eventType='sauve'; break;
            case 'annul_elmt_marin_parcours' : $eventType='annul'; break;
            case 'propos_elmt_marin_parcours' : $eventType='propos'; break;
            default:  $eventType='valid'; break;
           }
           $init = $selectedEvent->detail['init'] ?? [];
           $tab = $selectedEvent->detail[$eventType] ?? [];
           $restored =[];
           // Merger chaque type individuellement
           foreach(['activites','competences','savoirfaires','fonctions'] as $item){
             $initItems = $init[$item] ?? [];
             $tabItems = $tab[$item] ?? [];
             if ($eventType== 'annul'){
                $restored[$item]=array_diff_key($initItems,$tabItems);
             }else{
                $restored[$item]=$initItems + $tabItems;
             }

           }
           $this->selectedItems['restore']=$restored;
           $this->selectedItems['type']=$eventType;
           $this->selectedItems['commentaire']= $selectedEvent->detail['commentaire'] ?? 'Restore Data';
           $this->selectedItems['date_restore']= $selectedEvent->created_at->format('Y-m-d');
          // dd($this->selectedItems,$selectedEvent->detail);
        $this->save('restore');

        }
    }

  






    /**
     *
     * PARTIE SAUVEGARDE A REFAIRE AVEC EVENT
     */


    public function save(string $type)
    {
        //$record  = $this->fcmMarinModel::with('parcoursSerialises')->find($this->fcmMarinId);
        $record = $this->fcmMarin;

        if (!$record) {
            Notification::make()
                ->title('Erreur')
                ->body('Impossible de trouver le marin ou ses parcours.')
                ->danger()
                ->send();
            return;
        }


        switch ($type) {
            case "valid":
                $data = [
                    'commentaire' => $this->comment,
                    'selectedItems' => $this->selectedItems,
                    'key'           => 'sauve',
                    'marinUuid' => $record->marin->uuid,
                    'eventName' => 'valid',
                    'eventType' => UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
                    'objectUuid' => $record->parcoursSerialises[0]->uuid,
                ];
                $modal = 'sauve';
                break;
            case "annul":
                $data = [
                    'commentaire' => $this->comment,
                    'selectedItems' => $this->selectedItems,
                    'key'           => 'annul',
                    'marinUuid' => $record->marin->uuid,
                    'eventName' => 'annul',
                    'eventType' => UserGeneratedEventTypes::ANNUL_ELMT_MARIN_PARCOURS,
                    'objectUuid' => $record->parcoursSerialises[0]->uuid,
                ];
                $modal = 'annul';
                break;
            case "propos":
                $data = [
                    'commentaire' => $this->comment,
                    'selectedItems' => $this->selectedItems,
                    'key'           => 'propos',
                    'marinUuid' => $record->marin->uuid,
                    'eventName' => 'propos',
                    'eventType' => UserGeneratedEventTypes::PROPOS_ELMT_MARIN_PARCOURS,
                    'objectUuid' => $record->parcoursSerialises[0]->uuid,
                ];
                $modal = 'propos';
                break; 
            case "annulPropos":
                $data = [
                    'commentaire' => ' Annulation de la Proposition',
                    'selectedItems' => $this->selectedItems,
                    'key'           => 'aValiderPropos',
                    'marinUuid' => $record->marin->uuid,
                    'eventName' => 'annulPropos',
                    'eventType' => UserGeneratedEventTypes::ANNUL_PROPOS_ELMT_MARIN_PARCOURS,
                    'objectUuid' => $record->parcoursSerialises[0]->uuid,
                ];
                $modal = 'aValiderModal';
                break;   
            case "validPropos":
                $data = [
                    'commentaire' => ' Validation de la Proposition',
                    'selectedItems' => $this->selectedItems,
                    'key'           => 'aValiderPropos',
                    'marinUuid' => $record->marin->uuid,
                    'eventName' => 'validPropos',
                    'objectUuid' => $record->parcoursSerialises[0]->uuid,
                ];
                $modal = 'aValiderModal';
                break;    
            case "restore":
                $data = [
                    //'commentaire' => $this->comment,
                    'commentaire' => $this->selectedItems['commentaire'],
                    'selectedItems' => $this->selectedItems,
                    'marinUuid' => $record->marin->uuid,
                    'eventName' => 'restore',
                    'objectUuid' => $record->parcoursSerialises[0]->uuid,
                ];
                $modal = 'restoreModal';
                break;   
            case "reboot":
                    $data = [
                        'commentaire' => ' Reboot Data',
                        'selectedItems' => $this->selectedItems,
                        'marinUuid' => $record->marin->uuid,
                        'eventName' => 'reboot',
                        'objectUuid' => $record->parcoursSerialises[0]->uuid,
                    ];
                    $modal = 'rebootModal';
                    break;                                     
            default:
                $data =[];
                $modal = "sauve";
                break;

        }
        // Déclencher l'événement pour sauvegarder les données
        $key = $data["key"];
        $items_a_mettre_a_jour = $data["selectedItems"][$key];

        $marin = $this->fcmMarin->marin;
        //dd($items_a_mettre_a_jour["activites"]);
        foreach($items_a_mettre_a_jour["activites"] as $activite_a_mettre_a_jour => $bool)
        {

            $event = new $this->eventClass(
                event_type: $data['eventType'],
                user_uuid: auth()->user()->uuid,
                object_class: ObjectClasses::MARIN,
                object_uuid: $marin->uuid,
                detail: [
                    "type_element" => "activite", 
                    "uuid_element" => $activite_a_mettre_a_jour, 
                    "date" => Carbon::now(), 
                    "commentaire" => $this->comment, 
                ]
            );
            event($event);

        }
        //EventTriggerService::triggerEvent($data, $record);

        // Réinitialiser les données après la sauvegarde
        $this->selectedItems['sauve'] = [];
        $this->selectedItems['annul'] = [];
        $this->selectedItems['propos'] = [];
        $this->selectedItems['aValiderPropos'] = [];
        $this->selectedItems['restore'] = [];
        $this->comment = [];

        // Fermer le modal
        $this->dispatch('close-modal', id: $modal);

        // Rafraichir le Livewire
        //$this->dispatch('redirect-to-livret', id: $record->id);
        $this->js('window.location.reload()');
        // Recharge mais n'attends pas EventTrigger voir methode broadcats websockets echo
        //return redirect()->route('filament.admin.resources.marins.livrect-de-fcm', ['record'=> $record->id]);

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
