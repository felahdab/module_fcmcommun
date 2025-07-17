<?php


/**
 * Configuration centralisée pour les tests d'événements
 */
class EventTestConfig
{
    /**
     * Types de tests disponibles
     */
    public const TYPES_TESTS = [
        'MENTOR',
        'COHORTE',
        'MENTOR_COHORTE',
        'CREATION_PARCOURS_SERIALISE',
        'ATTRIBUER_PARCOURS_SERIALISE_MARIN',
        'ERREUR',
    ];

    /**
     * Messages de logs par type de test
     */
    public const LOGS_SPECIFIQUES = [
        'MENTOR' => 'EventBuilder Data handleMentor genere',
        'COHORTE' => 'EventBuilder Data handleCohorte genere',
        'CREATION_PARCOURS_SERIALISE' => 'EventBuilder Data handleCreationParcoursSerialise genere',
        'ATTRIBUER_PARCOURS_SERIALISE_MARIN' => 'EventBuilder Data handleParcoursSerialiseMarin genere',
    ];

    /**
     * Listeners par type de test
     */
    public const LISTENERS = [
        'MENTOR' => ['MentorMarinListener appele'],
        'COHORTE' => ['CohorteMarinListener appele'],
        'MENTOR_COHORTE' => ['MentorMarinListener appele', 'CohorteMarinListener appele']
    ];

    /**
     * Retourne les types de tests sous forme de dataset
     */
    public static function getDataset(): array
    {
        return self::TYPES_TESTS;
    }

    /**
     * Vérifie si un type de test nécessite un marinUuid
     */
    public static function needsMarinUuid(string $casTest): bool
    {
        return $casTest !== 'CREATION_PARCOURS_SERIALISE';
    }

    /**
     * Vérifie si un type de test crée des fcm_marin
     */
    public static function createsFcmMarin(string $casTest): bool
    {
        return !in_array($casTest, ['CREATION_PARCOURS_SERIALISE', 'ERREUR']);
    }

    /**
     * Retourne le nombre d'appels fcm_marin attendus
     */
    public static function getFcmMarinCallCount(string $casTest): int
    {
        return match ($casTest) {
            'MENTOR_COHORTE' => 2,
            'MENTOR', 'COHORTE', 'ATTRIBUER_PARCOURS_SERIALISE_MARIN' => 1,
            default => 0
        };
    }
}


