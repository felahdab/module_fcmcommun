<?php

namespace Modules\FcmCommun\Http\Livewire;

use Livewire\Component;
use Modules\FcmCommun\Traits\LoadsModelsDynamically;
use Filament\Notifications\Notification;
use Modules\FcmCommun\Services\EventTriggerService;
use Illuminate\Support\Facades\Log;
use Modules\FcmCommun\Services\HierarchyMapGeneratorService;
use Modules\FcmCommun\Services\ParcoursValidationService;


class LivretFcm extends Component
{

    use LoadsModelsDynamically;

    public $fcmMarinId;
    public $tablePrefix;
    public $fcmMarinModel;
    public $parcours;

    // État de l'interface
    public $selectedItems = [];
    public $openSections = [];
    public $comment = '';
    public $popupData = [];
    public $restoreId;
    public $restore = [];



    public function mount($fcmMarinId, $tablePrefix)
    {
        $this->fcmMarinId = $fcmMarinId;
        $this->tablePrefix = $tablePrefix;
        $this->fcmMarinModel = $this->getModelClass($this->tablePrefix, 'FcmMarin');
        
        $this->loadParcoursMarin();
        $this->loadRestoreData();
    }

    public function render()
    {
        return view('fcmcentral::filament.fcmcentral.livewire.parcours.parcours-details-validation', [
            'parcours' => $this->parcours,
        ]);
    }

    // ========================================
    // MÉTHODES PRINCIPALES SIMPLIFIÉES
    // ========================================

    public function toggleCheck(string $type, int $id)
    {
        $service = new ParcoursValidationService($this->selectedItems, $this->selectedItems['map']);
        $this->selectedItems = $service->toggleValidation($type, $id);
    }

    public function annulModal(string $type, int $id): void
    {
        $this->popupData = compact('type', 'id');
        $this->dispatch('open-modal', id: 'annul');
        
        $service = new ParcoursValidationService($this->selectedItems, $this->selectedItems['map']);
        $this->selectedItems = $service->toggleAnnulation($type, $id);
    }

    public function proposModal(string $type, int $id): void
    {
        $this->popupData = compact('type', 'id');
        $this->dispatch('open-modal', id: 'propos');
        
        $service = new ParcoursValidationService($this->selectedItems, $this->selectedItems['map']);
        $this->selectedItems = $service->toggleProposition($type, $id);
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

    // Modal Informations
    public function infosModal(string $type, int $id, string $dateValidation, string $displayName, string $commentaire, string $libelleCourt, string $libelleLong, string $duree,): void
    {
        $this->popupData = compact('type', 'id', 'dateValidation', 'displayName', 'commentaire', 'libelleCourt', 'libelleLong', 'duree');
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

    protected function loadParcoursMarin()
    {
        $fcmMarin = $this->fcmMarinModel::with('parcoursSerialises')->find($this->fcmMarinId);

        if ($fcmMarin && $fcmMarin->parcoursSerialises->isNotEmpty()) {
            $this->parcours = $this->formatParcours($fcmMarin->parcoursSerialises);
            $this->initializeSelectedItems();
        } else {
            $this->parcours = [];
            $this->selectedItems = [];
        }
    }

    protected function formatParcours($parcoursSerialises)
    {
        return $parcoursSerialises->map(function ($parcours) {
            return [
                'id' => $parcours->id,
                'libelle_court' => $parcours->libelle_court,
                'parcours' => json_decode($parcours->pivot->parcoursmarin, true),
                'taux_global' => $parcours->pivot->taux_global,
                'taux_stage' => $parcours->pivot->taux_stage,
                'taux_activite' => $parcours->pivot->taux_activite,
            ];
        })->toArray();
    }

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
            $this->selectedItems['init'][$type][$element['id']] = true;
            $this->selectedItems['gestion'][$type][$element['id']] = true;
        }
    }

    protected function loadRestoreData()
    {
        $fcmMarin = $this->fcmMarinModel::with('parcoursSerialises')->find($this->fcmMarinId);
        $storeEvents = $this->getModelClass($this->tablePrefix, 'StoredEvent');
        
        $this->restore = $storeEvents::where('event_type', 'valid_elmt_marin_parcours')
            ->where('marin_uuid', $fcmMarin->marin->uuid)
            ->where('object_uuid', $fcmMarin->parcoursSerialises[0]->uuid)
            ->get();
    }




    



    


   
    




















    

    











    /**
     *
     * PARTIE SAUVEGARDE A REFAIRE AVEC EVENT
     */


    public function save(string $type)
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


        switch ($type) {
            case "valid":
                $data = [
                    'commentaire' => $this->comment,
                    'selectedItems' => $this->selectedItems,
                    'marinUuid' => $record->marin->uuid,
                    'eventName' => 'valid',
                    'objectUuid' => $record->parcoursSerialises[0]->uuid,
                ];
                $modal = 'sauve';
                break;
            case "annul":
                $data = [
                    'commentaire' => $this->comment,
                    'selectedItems' => $this->selectedItems,
                    'marinUuid' => $record->marin->uuid,
                    'eventName' => 'annul',
                    'objectUuid' => $record->parcoursSerialises[0]->uuid,
                ];
                $modal = 'annul';
                break;
                case "propos":
                    $data = [
                        'commentaire' => $this->comment,
                        'selectedItems' => $this->selectedItems,
                        'marinUuid' => $record->marin->uuid,
                        'eventName' => 'propos',
                        'objectUuid' => $record->parcoursSerialises[0]->uuid,
                    ];
                    $modal = 'propos';
                    break; 
                default:
                $data =[];
                $modal = "sauve";
                break;

        }
        // Déclencher l'événement pour sauvegarder les données

        //dd($record,$data);
        EventTriggerService::triggerEvent($data, $record);

        // Réinitialiser les données après la sauvegarde
        $this->selectedItems['sauve'] = [];
        $this->selectedItems['annul'] = [];
        $this->selectedItems['propos'] = [];
        $this->comment = [];

        // Fermer le modal
        $this->dispatch('close-modal', id: $modal);

        // Rafraichir le Livewire
        //$this->dispatch('redirect-to-livret', id: $record->id);
        $this->js('window.location.reload()');
        // Recharge mais n'attends pas EventTrigger voir methode broadcats websockets echo
        //return redirect()->route('filament.admin.resources.marins.livrect-de-fcm', ['record'=> $record->id]);




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
