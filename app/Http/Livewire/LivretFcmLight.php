<?php

namespace Modules\FcmCommun\Http\Livewire;

use Carbon\Carbon;

use Illuminate\Support\Facades\Log;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;

use Filament\Notifications\Notification;

use Modules\FcmCommun\Traits\LoadsModelsDynamically;
use Modules\FcmCommun\Services\EventTriggerService;
use Modules\FcmCommun\Services\HierarchyMapGeneratorService;
use Modules\FcmCommun\Services\ParcoursValidationService;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCommun\Services\OutilsMarinParcoursService;

use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Events\UserGeneratedEvent;

class LivretFcmLight extends Component
{
    use LoadsModelsDynamically;

    #[Locked]
    public $marinId;

    private OutilsMarinParcoursService $service;

    public function mount(Marin $marin)
    {
        logger()->info("Mount");

        $this->marinId = $marin->id;
        logger()->info($this->marinId);

    }

    #[Computed]
    public function marin()
    {
        return Marin::find($this->marinId);
    }

    public function boot()
    {
        $this->service = new OutilsMarinParcoursService;
    }
 
    public function render()
    {
        $isCurrentUser = $this->marin->isCurrentUser();

        // Générer le mapping savoir-faire/enfants
        $savoirFaireChildren = $this->generateSavoirFaireChildrenMapping();
    
        
        return view('fcmcommun::livewire.livretfcmlight.livretfcmlight', [
            'marin' => $this->marin,
            'parcours' => $this->marin->complements_fcm->donnees_du_parcours->parcoursmarin,
            'isCurrentUser' => $isCurrentUser,
            'savoirFaireChildren' => $savoirFaireChildren, 
        ]);
    }

    private function generateSavoirFaireChildrenMapping(): array
    {
        $mapping = [];
        
        // Vérifier si le marin a un parcours
        if (!$this->marin->complements_fcm || 
            !$this->marin->complements_fcm->donnees_du_parcours ||
            !$this->marin->complements_fcm->donnees_du_parcours->parcoursmarin) {
            return $mapping;
        }
        
        $parcours = $this->marin->complements_fcm->donnees_du_parcours->parcoursmarin;
        
        foreach ($parcours['fonctions'] ?? [] as $fonction) {
            foreach ($fonction['competences'] ?? [] as $competence) {
                foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                    $savoirfaireUuid = $savoirfaire['uuid'];
                    $activiteUuids = [];
                    
                    foreach ($savoirfaire['activites'] ?? [] as $activite) {
                        $activiteUuids[] = $activite['uuid'];
                    }
                    
                    $mapping[$savoirfaireUuid] = $activiteUuids;
                }
            }
        }
        
        return $mapping;
    }
    


    public function devalider(array $element_a_devalider)
    {
        /**
         * [ 'type' => 'activite',
         *   'uuid' => '06a569b8-23a7-4c3f-b57a-51ca841894a7' 
         * ]
         */
        logger()->info("Dévalider: ");
        logger()->info($element_a_devalider);

        $event = new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::ANNUL_ELMT_MARIN_PARCOURS,
            user_uuid: auth()->user()->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $this->marin->uuid,
            detail: [
                "type_element" => $element_a_devalider['type'], 
                "uuid_element" => $element_a_devalider['uuid'], 
                "date" => Carbon::now(), 
                
            ]
        );
        event($event);

        $this->marin->refresh();

        $this->js('window.location.reload()');
    }

    public function valider(array $elements_a_valider, string $commentaire)
    {
        logger()->info($elements_a_valider);

        if (array_key_exists("savoirfaire", $elements_a_valider)){
            foreach($elements_a_valider["savoirfaire"] as $savoirfaire_uuid => $bool) {
                if (!$bool) continue; // Ignorer si non sélectionné
                
                $event = new UserGeneratedEvent(
                    event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
                    user_uuid: auth()->user()->uuid,
                    object_class: ObjectClasses::MARIN,
                    object_uuid: $this->marin->uuid,
                    detail: [
                        "type_element" => "savoirfaire", 
                        "uuid_element" => $savoirfaire_uuid, 
                        "date" => Carbon::now(), 
                        "commentaire" => $commentaire,
                    ]
                );
                event($event);
            }
        }

        if (array_key_exists("activite", $elements_a_valider)){
            foreach($elements_a_valider["activite"] as $activite_uuid => $bool) {
                if (!$bool) continue; // Ignorer si non sélectionné
                
                $event = new UserGeneratedEvent(
                    event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
                    user_uuid: auth()->user()->uuid,
                    object_class: ObjectClasses::MARIN,
                    object_uuid: $this->marin->uuid,
                    detail: [
                        "type_element" => "activite", 
                        "uuid_element" => $activite_uuid, 
                        "date" => Carbon::now(), 
                        "commentaire" => $commentaire,
                    ]
                );
                event($event);
            }
        }

        $this->marin->refresh();

// Vider le cache
//unset($this->marin);  
//$this->reset();     
// Rafraîchir le composant Livewire
//$this->dispatch('$refresh');
// Recharger la page 
$this->js('window.location.reload()');

    }

    public function proposer(array $element_a_proposer, string $commentaire)
    {
        /**
         * [ 'type' => 'activite',
         *   'uuid' => '06a569b8-23a7-4c3f-b57a-51ca841894a7' 
         * ]
         */
        logger()->info("Proposer: ");
        logger()->info($element_a_proposer);

        $event = new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::PROPOS_ELMT_MARIN_PARCOURS,
            user_uuid: auth()->user()->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $this->marin->uuid,
            detail: [
                "type_element" => $element_a_proposer['type'], 
                "uuid_element" => $element_a_proposer['uuid'], 
                "date" => Carbon::now(), 
                "commentaire" => $commentaire
            ]
        );
        event($event);

        $this->marin->refresh();
    }

    public function save(string $type)
    {
        $record = $this->fcmMarin;
        logger($this->selectedItemsSauve);
    
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

                $this->selectedItems = json_decode($this->selectedItemsSauve, true);

                $data = [
                    'commentaire' => $this->comment,
                    //'selectedItems' => $this->selectedItems,
                    'selectedItems' => $this->selectedItems['sauve'],
                    'key' => 'sauve',
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
                    'key' => 'annul',
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
                    'key' => 'propos',
                    'marinUuid' => $record->marin->uuid,
                    'eventName' => 'propos',
                    'eventType' => UserGeneratedEventTypes::PROPOS_ELMT_MARIN_PARCOURS,
                    'objectUuid' => $record->parcoursSerialises[0]->uuid,
                ];
                $modal = 'propos';
                break; 
            case "annulPropos":
                $data = [
                    'commentaire' => 'Annulation de la Proposition',
                    'selectedItems' => $this->selectedItems,
                    'key' => 'aValiderPropos',
                    'marinUuid' => $record->marin->uuid,
                    'eventName' => 'annulPropos',
                    'eventType' => UserGeneratedEventTypes::ANNUL_PROPOS_ELMT_MARIN_PARCOURS,
                    'objectUuid' => $record->parcoursSerialises[0]->uuid,
                ];
                $modal = 'aValiderModal';
                break;   
            case "validPropos":
                $data = [
                    'commentaire' => 'Validation de la Proposition',
                    'selectedItems' => $this->selectedItems,
                    'key' => 'aValiderPropos',
                    'marinUuid' => $record->marin->uuid,
                    'eventName' => 'validPropos',
                    'eventType' => UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
                    'objectUuid' => $record->parcoursSerialises[0]->uuid,
                ];
                $modal = 'aValiderModal';
                break;    
            case "restore":
                $data = [
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
                    'commentaire' => 'Reboot Data',
                    'selectedItems' => $this->selectedItems,
                    'marinUuid' => $record->marin->uuid,
                    'eventName' => 'reboot',
                    'objectUuid' => $record->parcoursSerialises[0]->uuid,
                ];
                $modal = 'rebootModal';
                break;                                     
            default:
                $data = [];
                $modal = "sauve";
                break;
    }

  
 
    //  UN SEUL ÉVÉNEMENT AVEC TOUS LES ÉLÉMENTS
    $key = $data["key"];
    $items_a_mettre_a_jour = $data["selectedItems"][$key] ?? [];
    //ddd($items_a_mettre_a_jour["activites"], $data['eventType'], $this->comment);
    $marin = $this->fcmMarin->marin;

    // TRAITER D'ABORD LES SAVOIR-FAIRES (priorité)
    foreach($items_a_mettre_a_jour["savoirfaires"] as $savoirfaire_uuid => $bool) {
    if (!$bool) continue; // Ignorer si non sélectionné
    
    //  ÉVÉNEMENT SAVOIR-FAIRE
    $event = new $this->eventClass(
        event_type: $data['eventType'],
        user_uuid: auth()->user()->uuid,
        object_class: ObjectClasses::MARIN,
        object_uuid: $marin->uuid,
        detail: [
            "type_element" => "savoirfaire", 
            "uuid_element" => $savoirfaire_uuid, 
            "date" => Carbon::now(), 
            "commentaire" => $this->comment,
            
        ]
    );
    event($event);
}

    //  TRAITER LES ACTIVITÉS INDIVIDUELLES (seulement celles hors savoir-faires validés)
    $activites_deja_traitees = $this->getActivitesTraiteesParSavoirfaires($items_a_mettre_a_jour["savoirfaires"]);
 
    foreach($items_a_mettre_a_jour["activites"] as $activite_uuid => $bool) {
    if (!$bool) continue; // Ignorer si non sélectionné
    
    //  VÉRIFIER SI L'ACTIVITÉ N'EST PAS DÉJÀ TRAITÉE PAR UN SAVOIR-FAIRE
    if (in_array($activite_uuid, $activites_deja_traitees)) {
        continue; // Ignorer car déjà traitée par le savoir-faire parent
    }

   
            $event = new $this->eventClass(
                event_type: $data['eventType'],
                user_uuid: auth()->user()->uuid,
                object_class: ObjectClasses::MARIN,
                object_uuid: $marin->uuid,
                detail: [
                    "type_element" => "activite", 
                    "uuid_element" => $activite_uuid, 
                    "date" => Carbon::now(), 
                    "commentaire" => $this->comment, 
                ]
            );
            event($event);

        }

}

        // ✅ MÉTHODE HELPER POUR IDENTIFIER LES ACTIVITÉS DÉJÀ TRAITÉES
        private function getActivitesTraiteesParSavoirfaires($savoirfaires_selectionnes): array
        {
            $activites_traitees = [];
            $parcours = $this->fcmMarin->marin->complements_fcm->donnees_du_parcours->parcoursmarin;
            
            foreach ($savoirfaires_selectionnes as $savoirfaire_uuid => $bool) {
                if (!$bool) continue;
                
                // Parcourir la structure pour trouver les activités de ce savoir-faire
                foreach ($parcours['fonctions'] as $fonction) {
                    if (!isset($fonction['competences'])) continue;
                    
                    foreach ($fonction['competences'] as $competence) {
                        if (!isset($competence['savoirfaires'])) continue;
                        
                        foreach ($competence['savoirfaires'] as $savoirfaire) {
                            if ($savoirfaire['uuid'] === $savoirfaire_uuid && isset($savoirfaire['activites'])) {
                                foreach ($savoirfaire['activites'] as $activite) {
                                    $activites_traitees[] = $activite['uuid'];
                                }
                                break 3; // Sortir de tous les loops
                            }
                        }
                    }
                }
            }
            
            return $activites_traitees;
        }

}
