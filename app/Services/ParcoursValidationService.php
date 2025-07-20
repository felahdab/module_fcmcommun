<?php

namespace Modules\FcmCommun\Services;

use Modules\FcmCommun\Helpers\HierarchyPropagatorHelper;
use Modules\FcmCommun\Helpers\HierarchyParentsVerifierHelper;


/**
 *  Service pour  LivretFCM : Pour la gestion de l'affichage checked/unchecked de la Page LivretFCM (sauve, annul, proposition, valid/annul proposition), 
 * 
 */
class ParcoursValidationService
{

    protected $selectedItems;
    protected $hierarchyMap;

    public function __construct(array $selectedItems, array $hierarchyMap)
    {
        $this->selectedItems = $selectedItems;
        $this->hierarchyMap = $hierarchyMap;
    }

    /**
     * Point d'entrée générique pour tous les toggles
     */
    public function toggle(string $action, string $type, string $uuid): array
    {
        $currentState = $this->getCurrentState($action, $type, $uuid);
        $newState = !$currentState;

        // 1. Mettre à jour l'élément principal
        $this->updateElementState($action, $type, $uuid, $newState);

        // 2. Propager aux descendants
        $this->propagateToDescendants($action, $type, $uuid, $newState);

        // 3. Vérifier et corriger les parents
        if (in_array($action,['validation','annulation'])){
        $this->verifyAllParents($action);
        }

        return $this->selectedItems;
    }

    /**
     * Méthodes spécifiques pour compatibilité
     */
    public function toggleValidation(string $type, string $uuid): array
    {
        return $this->toggle('validation', $type, $uuid);
    }

    public function toggleAnnulation(string $type, string $uuid): array
    {
        return $this->toggle('annulation', $type, $uuid);
    }

    public function toggleProposition(string $type, string $uuid): array
    {
        return $this->toggle('proposition', $type, $uuid);
    }

    /**
     * Obtient l'état actuel selon l'action
     */
    protected function getCurrentState(string $action, string $type, string $uuid): bool
    {
        switch ($action) {
            case 'validation':
                return isset($this->selectedItems['gestion'][$type][$uuid]);
            case 'annulation':
                return isset($this->selectedItems['annul'][$type][$uuid]);
            case 'proposition':
                return isset($this->selectedItems['propos'][$type][$uuid]);
        }
        return false;
    }

    /**
     * Met à jour l'état de l'élément principal
     */
    protected function updateElementState(string $action, string $type, string $uuid, bool $newState): void
    {
        $propagator = new HierarchyPropagatorHelper($this->selectedItems, $this->hierarchyMap);
        $propagator->updateChildState($type, $uuid, $action, $newState);
    }

    /**
     * Propage aux descendants
     */
    protected function propagateToDescendants(string $action, string $type, string $uuid, bool $state): void
    {
        $propagator = new HierarchyPropagatorHelper($this->selectedItems, $this->hierarchyMap);
        $this->selectedItems = $propagator->propagate($type, $uuid, $action, $state);
    }

    /**
     * Vérifie tous les parents
     */
    protected function verifyAllParents(string $action): void
    {
        $verifier = new HierarchyParentsVerifierHelper($this->selectedItems, $this->hierarchyMap);
        $this->selectedItems = $verifier->verifyAll($action);
    }

}


