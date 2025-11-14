<?php

namespace Modules\FcmCommun\Services;


/**
 *  Service pour  LivretFCM : Creation d'un map descendant et remontant pour les relations ManyToMany  et savoir qui est les parents et enfants
 *  Utilise surtout dans selectedItems du LIvewire FCMLivret
 * 
 */
class HierarchyMapGeneratorService
{

    /**
     * Génère une map enfant → parent à partir de fcmMarin->parcoursSerialize.
     *
     * @param array $parcours
     * @return array
     */
    public function generate(array $parcours): array
    {
        // Initialisation de la structure
        $parentMap = $this->initializeParentMap();
        // Traitement des fonctions et de leur hiérarchie
        $this->processHierarchy($parcours, $parentMap);
        // Nettoyage des doublons dans les relations remontantes
        $this->cleanupDuplicateRelations($parentMap);

        return $parentMap;
    }

    /**
     * Initialise la structure de base de la map parent-enfant
     *
     * @return array
     */
    protected function initializeParentMap(): array
    {
        $types = ['fonctions', 'competences', 'savoirfaires', 'activites'];
        $parentMap = [];

        foreach (['descendant', 'remontant'] as $direction) {
            $parentMap[$direction] = array_fill_keys($types, []);
        }

        return $parentMap;
    }


    /**
     * Traite la hiérarchie des éléments du parcours
     *
     * @param array $parcours
     * @param array &$parentMap
     */
    protected function processHierarchy(array $parcours, array &$parentMap): void
    {
        foreach ($parcours['fonctions'] ?? [] as $fonction) {
            $fId = $fonction['uuid'] ?? null;
            if (!$fId) continue;

            // Traitement de la fonction
            $this->processFonction($fonction, $fId, $parentMap);
        }
    }

    /**
     * Traite une fonction et ses éléments enfants
     *
     * @param array $fonction
     * @param int $fId
     * @param array &$parentMap
     */
    protected function processFonction(array $fonction, string $fId, array &$parentMap): void
    {
        $etatValid = $fonction['etat_valid'] ?? false;

        // Descendant Fonction : enfants = compétences
        $parentMap['descendant']['fonctions'][$fId] = [
            'etat_valid' => $etatValid,
            'competences' => [],
        ];

        // Remontant Fonction : pas de parent
        $parentMap['remontant']['fonctions'][$fId] = [
            'etat_valid' => $etatValid,
        ];

        // Traitement des compétences liées à cette fonction
        foreach ($fonction['competences'] ?? [] as $competence) {
            $cId = $competence['uuid'] ?? null;
            if (!$cId) continue;

            $this->processCompetence($competence, $cId, $fId, $parentMap);
        }
    }

    /**
     * Traite une compétence et ses éléments enfants
     *
     * @param array $competence
     * @param string $cId
     * @param string $fId
     * @param array &$parentMap
     */
    protected function processCompetence(array $competence, string $cId, string $fId, array &$parentMap): void
    {
        $etatValid = $competence['etat_valid'] ?? false;

        // Descendant Compétence : enfants = savoirfaires
        $parentMap['descendant']['competences'][$cId] = [
            'etat_valid' => $etatValid,
            'savoirfaires' => [],
        ];

        // Remontant Compétence : parents = fonctions
        $parentMap['remontant']['competences'][$cId] = [
            'etat_valid' => $etatValid,
            'fonctions' => [$fId],
        ];

        // Relier fonction → compétence descendant
        $parentMap['descendant']['fonctions'][$fId]['competences'][] = $cId;

        // Traitement des savoir-faire liés à cette compétence
        foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
            $sId = $savoirfaire['uuid'] ?? null;
            if (!$sId) continue;

            $this->processSavoirFaire($savoirfaire, $sId, $cId, $parentMap);
        }
    }

    /**
     * Traite un savoir-faire et ses éléments enfants
     *
     * @param array $savoirfaire
     * @param string $sId
     * @param string $cId
     * @param array &$parentMap
     */
    protected function processSavoirFaire(array $savoirfaire, string $sId, string $cId, array &$parentMap): void
    {
        $etatValid = $savoirfaire['etat_valid'] ?? false;

        // Descendant Savoir-faire : enfants = activités
        $parentMap['descendant']['savoirfaires'][$sId] = [
            'etat_valid' => $etatValid,
            'activites' => [],
        ];

        // Remontant Savoir-faire : parents = compétences (possiblement plusieurs)
        if (!isset($parentMap['remontant']['savoirfaires'][$sId])) {
            $parentMap['remontant']['savoirfaires'][$sId] = [
                'etat_valid' => $etatValid,
                'competences' => [],
            ];
        }
        $parentMap['remontant']['savoirfaires'][$sId]['competences'][] = $cId;

        // Relier compétence → savoirfaire descendant
        $parentMap['descendant']['competences'][$cId]['savoirfaires'][] = $sId;

        // Traitement des activités liées à ce savoir-faire
        foreach ($savoirfaire['activites'] ?? [] as $activite) {
            $aId = $activite['uuid'] ?? null;
            if (!$aId) continue;

            $this->processActivite($activite, $aId, $sId, $parentMap);
        }
    }

    /**
     * Traite une activité
     *
     * @param array $activite
     * @param string $aId
     * @param string $sId
     * @param array &$parentMap
     */
    protected function processActivite(array $activite, string $aId, string $sId, array &$parentMap): void
    {
        $etatValid = $activite['etat_valid'] ?? false;

        // Descendant Activité : pas d'enfants
        $parentMap['descendant']['activites'][$aId] = [
            'etat_valid' => $etatValid,
        ];

        // Remontant Activité : parents = savoirfaires (possiblement plusieurs)
        if (!isset($parentMap['remontant']['activites'][$aId])) {
            $parentMap['remontant']['activites'][$aId] = [
                'etat_valid' => $etatValid,
                'savoirfaires' => [],
            ];
        }
        $parentMap['remontant']['activites'][$aId]['savoirfaires'][] = $sId;

        // Relier savoirfaire → activité descendant
        $parentMap['descendant']['savoirfaires'][$sId]['activites'][] = $aId;
    }

    /**
     * Nettoie les doublons dans les relations remontantes
     *
     * @param array &$parentMap
     */
    protected function cleanupDuplicateRelations(array &$parentMap): void
    {
        $relationTypes = [
            'competences' => ['fonctions'],
            'savoirfaires' => ['competences'],
            'activites' => ['savoirfaires'],
        ];

        foreach ($relationTypes as $type => $parentTypes) {
            foreach ($parentMap['remontant'][$type] as $id => &$parents) {
                foreach ($parentTypes as $parentType) {
                    if (isset($parents[$parentType])) {
                        $parents[$parentType] = array_unique($parents[$parentType]);
                    }
                }
            }
        }
    }
}
