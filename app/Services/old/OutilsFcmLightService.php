<?php
// filepath: Modules/FcmCommun/Services/OutilsFcmLightService.php

namespace Modules\FcmCommun\Services;

use Modules\FcmCentral\Models\SavoirfaireType;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\Affectation;
use Illuminate\Support\Collection;

class OutilsFcmLightService
{
    /**
     * Cache des types pour éviter les requêtes répétées
     */
    private static ?Collection $savoirfaireTypes = null;

    /**
     * Cache des compétences pour éviter les requêtes répétées
     */
    private static ?Collection $competences = null;

    /**
     * Cache des affectations pour éviter les requêtes répétées
     */
    private static ?Collection $affectations = null;

    // ========================================
    // SAVOIR-FAIRE TYPES
    // ========================================

    /**
     * Récupère tous les types de savoir-faire depuis la base
     */
    public static function getSavoirfaireTypes(): Collection
    {
        if (self::$savoirfaireTypes === null) {
            self::$savoirfaireTypes = SavoirfaireType::orderBy('ordre')->get();
        }
       
        return self::$savoirfaireTypes;
    }

    /**
     * Récupère un type complet par son ID
     */
    public static function getSavoirfaireTypeById(int $typeId): ?SavoirfaireType
    {
        return self::getSavoirfaireTypes()->firstWhere('id', $typeId);
    }

    /**
     * Récupère les informations d'un type pour l'affichage
     */
    public static function getSavoirfaireTypeInfos(int $typeId): array
    {
        $type = self::getSavoirfaireTypeById($typeId);
       
        if (!$type) {
            return [
                'id' => $typeId,
                'code' => 'INCONNU',
                'color' => 'gray',
                'libelle_court' => 'Type inconnu',
                'libelle_long' => 'Type inconnu',
                'ordre' => 999
            ];
        }

        return [
            'id' => $type->id,
            'code' => $type->code,
            'color' => $type->color,
            'libelle_court' => $type->libelle_court,
            'libelle_long' => $type->libelle_long,
            'ordre' => $type->ordre
        ];
    }

    /**
     * Récupère tous les libellés courts indexés par ID
     */
    public static function getSavoirfaireTypeLibellesCourt(): array
    {
        return self::getSavoirfaireTypes()->pluck('libelle_court', 'id')->toArray();
    }

    // ========================================
    // COMPÉTENCES
    // ========================================

    /**
     * Récupère toutes les compétences depuis la base
     */
    public static function getCompetences(): Collection
    {
        if (self::$competences === null) {
            self::$competences = Competence::orderBy('code')->get();
        }
       
        return self::$competences;
    }

    /**
     * Récupère une compétence complète par son ID
     */
    public static function getCompetenceById(int $competenceId): ?Competence
    {
        return self::getCompetences()->firstWhere('id', $competenceId);
    }

    /**
     * Récupère les informations d'une compétence pour l'affichage
     */
    public static function getCompetenceInfos(int $competenceId): array
    {
        $competence = self::getCompetenceById($competenceId);
       
        if (!$competence) {
            return [
                'id' => $competenceId,
                'code' => 'INCONNU',
                'color' => 'gray',
                'libelle_court' => 'Compétence inconnue',
                'libelle_long' => 'Compétence inconnue',
            ];
        }

        return [
            'id' => $competence->id,
            'code' => $competence->code,
            'color' => $competence->color,
            'libelle_court' => $competence->libelle_court,
            'libelle_long' => $competence->libelle_long ?? $competence->libelle_court,
        ];
    }

    // ========================================
    // AFFECTATIONS
    // ========================================

    /**
     * Récupère toutes les affectations depuis la base
     */
    public static function getAffectations(): Collection
    {
        if (self::$affectations === null) {
            self::$affectations = Affectation::orderBy('ordre')->get();
        }
       
        return self::$affectations;
    }

    /**
     * Récupère une affectation complète par son ID
     */
    public static function getAffectationById(int $affectationId): ?Affectation
    {
        return self::getAffectations()->firstWhere('id', $affectationId);
    }

    /**
     * Récupère les informations d'une affectation pour l'affichage
     */
    public static function getAffectationInfos(int $affectationId): array
    {
        $affectation = self::getAffectationById($affectationId);
       
        if (!$affectation) {
            return [
                'id' => $affectationId,
                'code' => 'INCONNU',
                'color' => 'gray',
                'libelle_court' => 'Affectation inconnue',
                'libelle_long' => 'Affectation inconnue',
                'ordre' => 999
            ];
        }

        return [
            'id' => $affectation->id,
            'code' => $affectation->code ?? "AFF{$affectation->id}",
            'color' => $affectation->color,
            'libelle_court' => $affectation->libelle_court,
            'libelle_long' => $affectation->libelle_long ?? $affectation->libelle_court,
            'ordre' => $affectation->ordre
        ];
    }

    /**
     * Récupère tous les libellés courts des affectations indexés par ID
     */
    public static function getAffectationLibellesCourt(): array
    {
        return self::getAffectations()->pluck('libelle_court', 'id')->toArray();
    }

    // ========================================
    // GESTION DU CACHE
    // ========================================

    /**
     * Vide tous les caches
     */
    public static function clearCache(): void
    {
        self::$savoirfaireTypes = null;
        self::$competences = null;
        self::$affectations = null;
    }
}
