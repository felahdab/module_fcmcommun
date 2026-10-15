<?php

namespace Modules\FcmCommun\Http\Livewire\Concerns\LivretFcmLight;

use Carbon\Carbon;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

trait HasValidationActions
{
    public function devalider(array $element_a_devalider)
    {
        if ( ! $this->canAnnuler() )
        {
            logger()->warning("Un utilisateur n'ayant pas l'autorisation d annuler un élément du parcours a tenté de le faire.");
            abort(403, 'Vous n avez pas le droit de dévalider des éléments du parcours de FCM.');
        }

        logger()->info("Dévalider: ", $element_a_devalider);

        $event = new ($this->UserGeneratedEvent)(
            event_type: UserGeneratedEventTypes::ANNUL_ELMT_MARIN_PARCOURS,
            user_uuid: auth()->user()->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $this->marin->nid,
            detail: [
                "type_element" => $element_a_devalider['type'],
                "uuid_element" => $element_a_devalider['uuid'],
                "date" => Carbon::now(),
            ]
        );
        event($event);

        $this->refreshAfterAction();
    }

    public function valider(array $elements_a_valider, string $commentaire, ?string $dateValidation = null)
    {
        if ( ! $this->canValider() )
        {
            logger()->warning("Un utilisateur n'ayant pas l'autorisation de valider un élément du parcours a tenté de le faire.");
            abort(403, 'Vous n avez pas le droit de valider des éléments du parcours de FCM.');
        }

        $date = $dateValidation ? Carbon::parse($dateValidation) : Carbon::now();

        logger()->info("Valider: ", $elements_a_valider);

        $pieceJointePath = $this->stockerPieceJointe($elements_a_valider, $date);

        if (array_key_exists("savoirfaire", $elements_a_valider)){
            foreach($elements_a_valider["savoirfaire"] as $savoirfaire_uuid => $bool) {
                if (!$bool) continue;

                $this->createValidationEvent("savoirfaire", $savoirfaire_uuid, $commentaire, $date, $pieceJointePath);
            }
        }

        if (array_key_exists("objectif", $elements_a_valider)){
            foreach($elements_a_valider["objectif"] as $objectif_uuid => $bool) {
                if (!$bool) continue;

                $this->createValidationEvent("objectif", $objectif_uuid, $commentaire, $date, $pieceJointePath);
            }
        }

        $this->pieceJointe = null;

        $this->refreshAfterAction();
    }

    public function proposer(array $element_a_proposer, string $commentaire)
    {
        if ( ! $this->canProposer() )
        {
            logger()->warning("Un utilisateur n'ayant pas l'autorisation de proposer un élément du parcours a tenté de le faire.");
            abort(403, 'Vous n avez pas le droit de proposer des éléments du parcours de FCM.');
        }

        logger()->info("Proposer: ", $element_a_proposer);

        $event = new ($this->UserGeneratedEvent)(
            event_type: UserGeneratedEventTypes::PROPOS_ELMT_MARIN_PARCOURS,
            user_uuid: auth()->user()->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $this->marin->nid,
            detail: [
                "type_element" => $element_a_proposer['type'],
                "uuid_element" => $element_a_proposer['uuid'],
                "date" => Carbon::now(),
                "commentaire" => $commentaire
            ]
        );
        event($event);

        $this->refreshAfterAction();
    }

    //  méthode pour refuser une proposition
    public function annulerProposition(array $element_a_annuler, string $commentaire = '')
    {
        if (!$this->canRefuser()) {
            logger()->warning("Un utilisateur n'ayant pas l'autorisation d'annuler une proposition a tenté de le faire.");
            abort(403, 'Vous n\'avez pas le droit d\'annuler des propositions.');
        }

        logger()->info("Refuser proposition: ", $element_a_annuler);

        $event = new ($this->UserGeneratedEvent)(
            event_type: UserGeneratedEventTypes::ANNUL_PROPOS_ELMT_MARIN_PARCOURS,
            user_uuid: auth()->user()->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $this->marin->nid,
            detail: [
                "type_element" => $element_a_annuler['type'],
                "uuid_element" => $element_a_annuler['uuid'],
                "date" => Carbon::now(),
                "commentaire" => $commentaire
            ]
        );
        event($event);

        $this->refreshAfterAction();
    }

    // ========================================
    // MÉTHODES HELPER POUR ACTIONS
    // ========================================

    private function createValidationEvent(string $type, string $uuid, string $commentaire, ?Carbon $date = null, ?string $pieceJointePath = null): void
    {
        $detail = [
            "type_element"  => $type,
            "uuid_element"  => $uuid,
            "date"          => $date ?? Carbon::now(), // ✅ date antidatée
            "commentaire"   => $commentaire,
        ];

        if ($pieceJointePath) {
            $detail["piece_jointe"] = $pieceJointePath;
        }

        $event = new ($this->UserGeneratedEvent)(
            event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
            user_uuid: auth()->user()->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $this->marin->nid,
            detail: $detail
        );
        event($event);
    }

    private function refreshAfterAction(): void
    {
        $this->marin->refresh();

        unset($this->parcours);
        unset($this->parcoursFiltre);
        unset($this->statistiques);

        $this->dispatch('clear-selections');
        $this->dispatch('$refresh');
    }
}
