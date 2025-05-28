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
     * Traite l'action d'un élément spécifique (annulation ou proposition)
     */
    public function handleSingleAction(string $action, string $type, int $id): array
    {
        if (!isset($this->hierarchyMap['remontant'][$type][$id])) {
            return $this->selectedItems;
        }

        $elementData = $this->hierarchyMap['remontant'][$type][$id];
        
        // Parcourir tous les types de parents possibles
        foreach ($elementData as $key => $parentIds) {
            if ($key === 'etat_valid') continue; // Ignorer etat_valid
            
            $parentType = $key; // competences, fonctions, etc.
            
            // S'assurer que parentIds est un tableau
            if (!is_array($parentIds)) {
                $parentIds = [$parentIds];
            }
            
            // Appliquer l'action sur chaque parent
            foreach ($parentIds as $parentId) {
                $this->applyActionOnParent($action, $parentType, $parentId);
            }
        }
        
        return $this->selectedItems;
    }

    /**
     * Traite l'annulation d'un élément spécifique
     */
    public function handleSingleAnnulation(string $type, int $id): array
    {
        return $this->handleSingleAction('annulation', $type, $id);
    }

    /**
     * Traite la proposition d'un élément spécifique
     */
    public function handleSingleProposition(string $type, int $id): array
    {
        return $this->handleSingleAction('proposition', $type, $id);
    }

    /**
     * Vérifie tous les parents selon l'action
     */
    public function verifyAll(string $action): array
    {
        if (in_array($action, ['annulation', 'proposition'])) {
            $this->handleAllLevelsForAction($action);
        } else {
            // Remonter la hiérarchie depuis les feuilles pour validation
            $this->verifyLevel('savoirfaires', 'activites', $action);
            $this->verifyLevel('competences', 'savoirfaires', $action);
            $this->verifyLevel('fonctions', 'competences', $action);
        }
        
        return $this->selectedItems;
    }

    /**
     * Gère tous les niveaux de hiérarchie pour une action donnée
     */
    protected function handleAllLevelsForAction(string $action): void
    {
        // Ordre de traitement : du plus bas vers le plus haut
        $levels = ['activites', 'savoirfaires', 'competences', 'fonctions'];
        
        foreach ($levels as $childType) {
            $this->handleLevelForAction($action, $childType);
        }
    }

    /**
     * Gère un niveau pour une action donnée (annulation ou proposition)
     */
    protected function handleLevelForAction(string $action, string $childType): void
    {
        if (!isset($this->hierarchyMap['remontant'][$childType])) return;

        // Parcourir tous les éléments de ce type ayant l'action correspondante
        $actionKey = $action === 'annulation' ? 'annul' : 'propos';
        if (!isset($this->selectedItems[$actionKey][$childType])) return;

        foreach ($this->selectedItems[$actionKey][$childType] as $childId => $value) {
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
                    
                    // Appliquer l'action sur chaque parent
                    foreach ($parentIds as $parentId) {
                        $this->applyActionOnParent($action, $parentType, $parentId);
                    }
                }
            }
        }
    }

    /**
     * Applique une action sur un parent (annulation ou proposition)
     */
    protected function applyActionOnParent(string $action, string $type, int $parentId): void
    {
        if ($action === 'annulation') {
            $this->devalidateParentIfValid($type, $parentId);
        } elseif ($action === 'proposition') {
            $this->proposeParentIfEligible($type, $parentId);
        }
    }

    /**
     * Vérifie un niveau de la hiérarchie
     */
    protected function verifyLevel(string $parentType, string $childType, string $action): void
    {
        if (in_array($action, ['annulation', 'proposition'])) {
            $this->handleLevelForAction($action, $childType);
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
        if ($action === 'validation') {
            $this->updateParentValidationState($type, $id, $shouldUpdate);
        } elseif ($action === 'proposition') {
            $this->updateParentPropositionState($type, $id, $shouldUpdate);
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
     * Dévalide un parent s'il est actuellement valide et l'ajoute dans annul
     */
    protected function devalidateParentIfValid(string $type, int $parentId): void
    {
        // Vérifier si le parent est actuellement valide (dans gestion OU dans init)
        $isValid = isset($this->selectedItems['gestion'][$type][$parentId]) || 
                   isset($this->selectedItems['init'][$type][$parentId]);
        
        if ($isValid) {
            // Ajouter le parent dans annul
            $this->selectedItems['annul'][$type][$parentId] = true;
            
            // Nettoyer les autres états
            unset($this->selectedItems['gestion'][$type][$parentId]);
            unset($this->selectedItems['sauve'][$type][$parentId]);
            unset($this->selectedItems['propos'][$type][$parentId]);
            
            // Mettre à jour la map
            $this->updateMapState($type, $parentId, false);
        }
    }

    /**
     * Propose un parent s'il est éligible et l'ajoute dans propos
     */
    protected function proposeParentIfEligible(string $type, int $parentId): void
    {
        // Vérifier si le parent n'est pas déjà valide ou annulé
        $isValid = isset($this->selectedItems['gestion'][$type][$parentId]) || 
                   isset($this->selectedItems['init'][$type][$parentId]);
        $isAnnul = isset($this->selectedItems['annul'][$type][$parentId]);
        
        // Si le parent n'est ni valide ni annulé, on peut le proposer
        if (!$isValid && !$isAnnul) {
            // Ajouter le parent dans propos
            $this->selectedItems['propos'][$type][$parentId] = true;
            
            // Mettre à jour la map
            $this->updateMapState($type, $parentId, 'proposed');
        }
    }

    /**
     * Met à jour l'état de proposition du parent
     */
    protected function updateParentPropositionState(string $type, int $id, bool $shouldBeProposed): void
    {
        $parentValid = isset($this->selectedItems['gestion'][$type][$id]) || 
                       isset($this->selectedItems['init'][$type][$id]);
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
