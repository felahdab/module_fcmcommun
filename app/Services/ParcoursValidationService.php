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
    public function toggle(string $action, string $type, int $id): array
    {
        $currentState = $this->getCurrentState($action, $type, $id);
        $newState = !$currentState;

        // 1. Mettre à jour l'élément principal
        $this->updateElementState($action, $type, $id, $newState);

        // 2. Propager aux descendants
        $this->propagateToDescendants($action, $type, $id, $newState);

        // 3. Vérifier et corriger les parents
        if (in_array($action,['validation','annulation'])){
        $this->verifyAllParents($action);
        }

        return $this->selectedItems;
    }

    /**
     * Méthodes spécifiques pour compatibilité
     */
    public function toggleValidation(string $type, int $id): array
    {
        return $this->toggle('validation', $type, $id);
    }

    public function toggleAnnulation(string $type, int $id): array
    {
        return $this->toggle('annulation', $type, $id);
    }

    public function toggleProposition(string $type, int $id): array
    {
        return $this->toggle('proposition', $type, $id);
    }

    /**
     * Obtient l'état actuel selon l'action
     */
    protected function getCurrentState(string $action, string $type, int $id): bool
    {
        switch ($action) {
            case 'validation':
                return isset($this->selectedItems['gestion'][$type][$id]);
            case 'annulation':
                return isset($this->selectedItems['annul'][$type][$id]);
            case 'proposition':
                return isset($this->selectedItems['propos'][$type][$id]);
        }
        return false;
    }

    /**
     * Met à jour l'état de l'élément principal
     */
    protected function updateElementState(string $action, string $type, int $id, bool $newState): void
    {
        $propagator = new HierarchyPropagatorHelper($this->selectedItems, $this->hierarchyMap);
        $propagator->updateChildState($type, $id, $action, $newState);
    }

    /**
     * Propage aux descendants
     */
    protected function propagateToDescendants(string $action, string $type, int $id, bool $state): void
    {
        $propagator = new HierarchyPropagatorHelper($this->selectedItems, $this->hierarchyMap);
        $this->selectedItems = $propagator->propagate($type, $id, $action, $state);
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


