<?php

namespace Modules\FcmCommun\Http\Livewire;

use Carbon\Carbon;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;

use Modules\FcmCommun\Traits\LoadsModelsDynamically;
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

}
