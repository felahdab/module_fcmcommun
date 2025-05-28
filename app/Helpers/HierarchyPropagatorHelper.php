<?php

namespace Modules\FcmCommun\Helpers;

class HierarchyPropagatorHelper
{
    protected $selectedItems;
    protected $hierarchyMap;
    
    protected $childrenTypes = [
        'fonctions' => 'competences',
        'competences' => 'savoirfaires',
        'savoirfaires' => 'activites',
        'activites' => null,
    ];

    public function __construct(array &$selectedItems, array $hierarchyMap)
    {
        $this->selectedItems = &$selectedItems;
        $this->hierarchyMap = $hierarchyMap;
    }

    /**
     * Propage un état vers les descendants
     */
    public function propagate(string $type, int $id, string $action, bool $state): array
    {
        $childrenType = $this->childrenTypes[$type] ?? null;
        if (!$childrenType || !isset($this->hierarchyMap['descendant'][$type][$id][$childrenType])) {
            return $this->selectedItems;
        }

        foreach ($this->hierarchyMap['descendant'][$type][$id][$childrenType] as $childId) {
            $this->updateChildState($childrenType, $childId, $action, $state);
            $this->propagate($childrenType, $childId, $action, $state);
        }

        return $this->selectedItems;
    }

    /**
     * Met à jour l'état d'un enfant selon l'action
     */
    public function updateChildState(string $type, int $id, string $action, bool $state): void
    {
        switch ($action) {
            case 'validation':
                $this->updateValidationState($type, $id, $state);
                break;
            case 'annulation':
                $this->updateAnnulationState($type, $id, $state);
                break;
            case 'proposition':
                $this->updatePropositionState($type, $id, $state);
                break;
        }
    }

    /**
     * Mise à jour pour validation
     */
    protected function updateValidationState(string $type, int $id, bool $state): void
    {
        $this->updateMapState($type, $id, $state);

        if ($state) {
            $this->selectedItems['gestion'][$type][$id] = true;
            if (!isset($this->selectedItems['init'][$type][$id])) {
                $this->selectedItems['sauve'][$type][$id] = true;
            }
        } else {
            if (!isset($this->selectedItems['init'][$type][$id])) {
                unset($this->selectedItems['gestion'][$type][$id]);
            }
            unset($this->selectedItems['sauve'][$type][$id]);
        }
    }

    /**
     * Mise à jour pour annulation
     */
    protected function updateAnnulationState(string $type, int $id, bool $annulState): void
    {
        $this->updateMapState($type, $id, !$annulState);

        if ($annulState) {
            $this->selectedItems['annul'][$type][$id] = true;
            if (!isset($this->selectedItems['init'][$type][$id])) {
                unset($this->selectedItems['gestion'][$type][$id]);
            }
            unset($this->selectedItems['sauve'][$type][$id]);
            unset($this->selectedItems['propos'][$type][$id]);
        } else {
            unset($this->selectedItems['annul'][$type][$id]);
            if (isset($this->selectedItems['init'][$type][$id])) {
                $this->selectedItems['gestion'][$type][$id] = true;
            }
        }
    }

    /**
     * Mise à jour pour proposition
     */
    protected function updatePropositionState(string $type, int $id, bool $proposState): void
    {
        // Ne proposer que si pas déjà validé ou annulé
        $isValid = isset($this->selectedItems['gestion'][$type][$id]) || isset($this->selectedItems['init'][$type][$id]);
        $isAnnul = isset($this->selectedItems['annul'][$type][$id]);

        if ($isValid || $isAnnul) {
            return;
        }

        if ($proposState) {
            $this->selectedItems['propos'][$type][$id] = true;
            $this->updateMapState($type, $id, 'proposed');
        } else {
            unset($this->selectedItems['propos'][$type][$id]);
            $this->updateMapState($type, $id, false);
        }
    }

    /**
     * Met à jour l'état dans la map
     */
    protected function updateMapState(string $type, int $id, $state): void
    {
        if (isset($this->hierarchyMap['descendant'][$type][$id])) {
            $this->hierarchyMap['descendant'][$type][$id]['etat_valid'] = $state;
        }
        if (isset($this->hierarchyMap['remontant'][$type][$id])) {
            $this->hierarchyMap['remontant'][$type][$id]['etat_valid'] = $state;
        }
    }



}
