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
    public function propagate(string $type, string $uuid, string $action, bool $state): array
    {
        $childrenType = $this->childrenTypes[$type] ?? null;
        if (!$childrenType || !isset($this->hierarchyMap['descendant'][$type][$uuid][$childrenType])) {
            return $this->selectedItems;
        }

        foreach ($this->hierarchyMap['descendant'][$type][$uuid][$childrenType] as $childId) {
            $this->updateChildState($childrenType, $childId, $action, $state);
            $this->propagate($childrenType, $childId, $action, $state);
        }

        return $this->selectedItems;
    }

    /**
     * Met à jour l'état d'un enfant selon l'action
     */
    public function updateChildState(string $type, string $uuid, string $action, bool $state): void
    {
        switch ($action) {
            case 'validation':
                $this->updateValidationState($type, $uuid, $state);
                break;
            case 'annulation':
                $this->updateAnnulationState($type, $uuid, $state);
                break;
            case 'proposition':
                $this->updatePropositionState($type, $uuid, $state);
                break;
        }
    }

    /**
     * Mise à jour pour validation
     */
    protected function updateValidationState(string $type, string $uuid, bool $state): void
    {
        $this->updateMapState($type, $uuid, $state);

        if ($state) {
            $this->selectedItems['gestion'][$type][$uuid] = true;
            if (!isset($this->selectedItems['init'][$type][$uuid])) {
                $this->selectedItems['sauve'][$type][$uuid] = true;
            }
        } else {
            if (!isset($this->selectedItems['init'][$type][$uuid])) {
                unset($this->selectedItems['gestion'][$type][$uuid]);
            }
            unset($this->selectedItems['sauve'][$type][$uuid]);
        }
    }

    /**
     * Mise à jour pour annulation
     */
    protected function updateAnnulationState(string $type, string $uuid, bool $annulState): void
    {
        $this->updateMapState($type, $uuid, !$annulState);

        if ($annulState) {
            $this->selectedItems['annul'][$type][$uuid] = true;
            if (!isset($this->selectedItems['init'][$type][$uuid])) {
                unset($this->selectedItems['gestion'][$type][$uuid]);
            }
            unset($this->selectedItems['sauve'][$type][$uuid]);
            unset($this->selectedItems['propos'][$type][$uuid]);
        } else {
            unset($this->selectedItems['annul'][$type][$uuid]);
            if (isset($this->selectedItems['init'][$type][$uuid])) {
                $this->selectedItems['gestion'][$type][$uuid] = true;
            }
        }
    }

    /**
     * Mise à jour pour proposition
     */
    protected function updatePropositionState(string $type, string $uuid, bool $proposState): void
    {
        // Ne proposer que si pas déjà validé ou annulé
        $isValid = isset($this->selectedItems['gestion'][$type][$uuid]) || isset($this->selectedItems['init'][$type][$uuid]);
        $isAnnul = isset($this->selectedItems['annul'][$type][$uuid]);

        if ($isValid || $isAnnul) {
            return;
        }

        if ($proposState) {
            $this->selectedItems['propos'][$type][$uuid] = true;
            $this->updateMapState($type, $uuid, 'proposed');
        } else {
            unset($this->selectedItems['propos'][$type][$uuid]);
            $this->updateMapState($type, $uuid, false);
        }
    }

    /**
     * Met à jour l'état dans la map
     */
    protected function updateMapState(string $type, string $uuid, $state): void
    {
        if (isset($this->hierarchyMap['descendant'][$type][$uuid])) {
            $this->hierarchyMap['descendant'][$type][$uuid]['etat_valid'] = $state;
        }
        if (isset($this->hierarchyMap['remontant'][$type][$uuid])) {
            $this->hierarchyMap['remontant'][$type][$uuid]['etat_valid'] = $state;
        }
    }



}
