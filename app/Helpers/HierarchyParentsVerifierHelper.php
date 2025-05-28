<?php

namespace Modules\FcmCommun\Helpers;

class HierarchyParentsVerifierHelper
{
    protected $selectedItems;
    protected $hierarchyMap;

    protected $hierarchyLevels = [
        'activites' => 'savoirfaires',
        'savoirfaires' => 'competences', 
        'competences' => 'fonctions',
        'fonctions' => null,
    ];

    public function __construct(array &$selectedItems, array $hierarchyMap)
    {
        $this->selectedItems = &$selectedItems;
        $this->hierarchyMap = $hierarchyMap;
    }



        /**
     * Traite l'annulation d'un élément spécifique
     * À utiliser quand on vient d'ajouter UN élément dans annul
     */
    public function handleSingleAnnulation(string $type, int $id): array
    {
        // Traiter uniquement cet élément spécifique
        if (isset($this->hierarchyMap['remontant'][$type][$id])) {
            $elementData = $this->hierarchyMap['remontant'][$type][$id];
            
            // Parcourir tous les types de parents possibles
            foreach ($elementData as $key => $parentIds) {
                if ($key === 'etat_valid') continue; // Ignorer etat_valid
                
                $parentType = $key; // competences, fonctions, etc.
                
                // S'assurer que parentIds est un tableau
                if (!is_array($parentIds)) {
                    $parentIds = [$parentIds];
                }
                
                // Dévalider chaque parent qui est actuellement valide
                foreach ($parentIds as $parentId) {
                    $this->devalidateParentIfValid($parentType, $parentId);
                }
            }
        }
        
        return $this->selectedItems;
    }


    
    /**
     * Vérifie tous les parents selon l'action
     */
    public function verifyAll(string $action): array
    {
        if ($action === 'annulation') {
            // Pour l'annulation, on traite tous les niveaux en une fois
            $this->handleAllAnnulationLevels();
        } else {
            // Remonter la hiérarchie depuis les feuilles
            $this->verifyLevel('savoirfaires', 'activites', $action);
            $this->verifyLevel('competences', 'savoirfaires', $action);
            $this->verifyLevel('fonctions', 'competences', $action);
        }
        
        return $this->selectedItems;
    }

    /**
     * Gère l'annulation pour tous les niveaux de hiérarchie
     */
    protected function handleAllAnnulationLevels(): void
    {
        // Ordre de traitement : du plus bas vers le plus haut
        $levels = ['activites', 'savoirfaires', 'competences', 'fonctions'];
        
        foreach ($levels as $childType) {
            $this->handleAnnulationLevel($childType);
        }
    }

    /**
     * Vérifie un niveau de la hiérarchie
     */
    protected function verifyLevel(string $parentType, string $childType, string $action): void
    {
        if ($action === 'annulation') {
            // Pour l'annulation, on utilise une logique spéciale avec la map remontant
            $this->handleAnnulationLevel($childType);
            return;
        }

        if (!isset($this->hierarchyMap['descendant'][$parentType])) return;

        foreach ($this->hierarchyMap['descendant'][$parentType] as $parentId => $data) {
            if (!isset($data[$childType]) || empty($data[$childType])) continue;

            $shouldUpdate = $this->shouldParentBeUpdated($childType, $data[$childType], $action);
            $this->updateParentState($parentType, $parentId, $action, $shouldUpdate);
        }
    }

    /**
     * Détermine si un parent doit être mis à jour selon l'action
     */
    protected function shouldParentBeUpdated(string $childType, array $childrenIds, string $action): bool
    {
        switch ($action) {
            case 'validation':
                return $this->areAllChildrenChecked($childType, $childrenIds);
            
            case 'annulation':
                return $this->shouldParentBeAnnulated($childType, $childrenIds);
            
            case 'proposition':
                return $this->shouldParentBeProposed($childType, $childrenIds);
        }
        
        return false;
    }

    /**
     * Vérifie si tous les enfants sont cochés (validation)
     */
    protected function areAllChildrenChecked(string $type, array $childrenIds): bool
    {
        foreach ($childrenIds as $childId) {
            if (!isset($this->selectedItems['gestion'][$type][$childId])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Détermine si le parent doit être annulé
     * CORRECTION: Utilise la map remontant pour identifier les parents à dévalider
     */
    protected function shouldParentBeAnnulated(string $type, array $childrenIds): bool
    {
        // Pour l'annulation, on va utiliser directement la logique dans updateParentAnnulationState
        // Cette méthode n'est plus utilisée pour l'annulation
        return false;
    }

    /**
     * Détermine si le parent doit être proposé
     */
    protected function shouldParentBeProposed(string $type, array $childrenIds): bool
    {
        foreach ($childrenIds as $childId) {
            if (isset($this->selectedItems['propos'][$type][$childId])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Met à jour l'état du parent selon l'action
     */
    protected function updateParentState(string $type, int $id, string $action, bool $shouldUpdate): void
    {
        switch ($action) {
            case 'validation':
                $this->updateParentValidationState($type, $id, $shouldUpdate);
                break;
            
            case 'annulation':
               // $this->updateParentAnnulationState($type, $id, $shouldUpdate);
                break;
            
            case 'proposition':
                $this->updateParentPropositionState($type, $id, $shouldUpdate);
                break;
        }
    }

    /**
     * Met à jour l'état de validation du parent
     */
    protected function updateParentValidationState(string $type, int $id, bool $allChildrenChecked): void
    {
        $this->updateMapState($type, $id, $allChildrenChecked);

        if ($allChildrenChecked) {
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
     * Gère l'annulation en utilisant la map remontant
     */
    protected function handleAnnulationLevel(string $childType): void
    {
        if (!isset($this->hierarchyMap['remontant'][$childType])) return;

        // Parcourir tous les éléments annulés de ce type
        if (!isset($this->selectedItems['annul'][$childType])) return;

        foreach ($this->selectedItems['annul'][$childType] as $childId => $value) {
            // Trouver les parents de cet élément annulé dans la map remontant
            if (isset($this->hierarchyMap['remontant'][$childType][$childId])) {
                $childData = $this->hierarchyMap['remontant'][$childType][$childId];
                
                // Parcourir tous les types de parents possibles
                foreach ($childData as $key => $parentIds) {
                    if ($key === 'etat_valid') continue; // Ignorer etat_valid
                    
                    $parentType = $key; // competences, fonctions, etc.
                    
                    // S'assurer que parentIds est un tableau
                    if (!is_array($parentIds)) {
                        $parentIds = [$parentIds];
                    }
                    
                    // Dévalider chaque parent qui est actuellement valide
                    foreach ($parentIds as $parentId) {
                        $this->devalidateParentIfValid($parentType, $parentId);
                    }
                }
            }
        }
    }

    /**
     * Dévalide un parent s'il est actuellement valide et l'ajoute dans annul
     */
    protected function devalidateParentIfValid(string $type, int $parentId): void
    {
        // Vérifier si le parent est actuellement valide (dans gestion OU dans init)
        $isInGestion = isset($this->selectedItems['gestion'][$type][$parentId]);
        $isInInit = isset($this->selectedItems['init'][$type][$parentId]);
        
        if ($isInGestion || $isInInit) {
            // IMPORTANT: Ajouter le parent dans annul
            $this->selectedItems['annul'][$type][$parentId] = true;
            
            // Retirer de gestion (même si c'était dans init)
            unset($this->selectedItems['gestion'][$type][$parentId]);
            unset($this->selectedItems['sauve'][$type][$parentId]);
            unset($this->selectedItems['propos'][$type][$parentId]);
            
            // Mettre à jour la map
            $this->updateMapState($type, $parentId, false);
        }
    }

    /**
     * Met à jour l'état de proposition du parent
     */
    protected function updateParentPropositionState(string $type, int $id, bool $shouldBeProposed): void
    {
        $parentValid = isset($this->selectedItems['gestion'][$type][$id]) || isset($this->selectedItems['init'][$type][$id]);
        $parentAnnul = isset($this->selectedItems['annul'][$type][$id]);

        if ($shouldBeProposed && !$parentValid && !$parentAnnul) {
            $this->selectedItems['propos'][$type][$id] = true;
            $this->updateMapState($type, $id, 'proposed');
        } elseif (!$shouldBeProposed && !$parentValid && !$parentAnnul) {
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
