<?php

namespace Modules\FcmCommun\Services;

use Modules\FcmCentral\Models\SavoirfaireType;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\Annee;
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
     * Cache des années pour éviter les requêtes répétées
     */
    private static ?Collection $annees = null;

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
     * Récupère les informations d'un type pour l'affichage (recherche)
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
     * Récupère les informations d'une compétence pour l'affichage (recherche)
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
    // ANNÉES
    // ========================================

    /**
     * Récupère toutes les années depuis la base
     */
    public static function getAnnees(): Collection
    {
        if (self::$annees === null) {
            self::$annees = Annee::orderBy('ordre')->get();
        }
       
        return self::$annees;
    }

    /**
     * Récupère une année complète par son ID
     */
    public static function getAnneeById(int $anneeId): ?Annee
    {
        return self::getAnnees()->firstWhere('id', $anneeId);
    }

    /**
     * Récupère les informations d'une année pour l'affichage (recherche)
     */
    public static function getAnneeInfos(int $anneeId): array
    {
        $annee = self::getAnneeById($anneeId);
       
        if (!$annee) {
            return [
                'id' => $anneeId,
                'code' => 'INCONNU',
                'color' => 'gray',
                'libelle_court' => 'Année inconnue',
                'libelle_long' => 'Année inconnue',
                'ordre' => 999
            ];
        }

        return [
            'id' => $annee->id,
            'code' => $annee->code ?? "A{$annee->id}",
            'color' => $annee->color,
            'libelle_court' => $annee->libelle_court,
            'libelle_long' => $annee->libelle_long ?? $annee->libelle_court,
            'ordre' => $annee->ordre
        ];
    }

    /**
     * Récupère tous les libellés courts des années indexés par ID
     */
    public static function getAnneeLibellesCourt(): array
    {
        return self::getAnnees()->pluck('libelle_court', 'id')->toArray();
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
        self::$annees = null;
    }
}
