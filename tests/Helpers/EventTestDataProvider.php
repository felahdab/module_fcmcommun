<?php


use Carbon\Carbon;
use Modules\FcmCommun\Services\EventTypeManager;
use Modules\RH\Models\Marin;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCentral\Database\Seeders\Tests\TestParcoursSeeder;

class EventTestDataProvider
{
    /**
     * Prépare les données selon le type de test
     */
    public static function preparerDonneesTest(string $casTest, Marin $marin): array
    {
        return match ($casTest) {
            'MENTOR' => self::preparerCasMentor($marin),
            'COHORTE' => self::preparerCasCohorte($marin),
            'MENTOR_COHORTE' => self::preparerCasMentorCohorte($marin),
            'CREATION_PARCOURS_SERIALISE' => self::preparerCasCreationParcours(),
            'ATTRIBUER_PARCOURS_SERIALISE_MARIN' => self::preparerCasAttributionParcours($marin),
            'ERREUR' => self::preparerCasErreur($marin),
            default => self::preparerCasDefault($marin),
        };
    }

    private static function preparerCasMentor(Marin $marin): array
    {
        $mentor = Marin::factory()->create(['nom' => 'Commandant Florian']);
        
        return [
            'setup' => [
                'mentor' => $mentor, 
                'record' => $marin
            ],
            'data' => [
                'mentor_id' => $mentor->id,
                'marinUuid' => $marin->uuid
            ],
            'evenementsAttendus' => [EventTypeManager::ATTRIBUER_MENTOR_MARIN],
            'assertionsPersonnalisees' => [
                'evenement' => [
                    'detail_mentor_nom' => 'Commandant Florian',
                    'object_class' => 'FcmMarin'
                ],
                'integration' => [
                    'stored_event_count' => 1,
                    'fcm_marin_count' => 1,
                    'mentor_id_match' => true,
                    'rh_marin_id_match' => true
                ]
            ]
        ];
    }

    private static function preparerCasCohorte(Marin $marin): array
    {
        $cohorte = Cohorte::factory()->create(['libelle_court' => '22.1']);
        
        return [
            'setup' => [
                'cohorte' => $cohorte,
                'record' => $marin
            ],
            'data' => [
                'cohorte_id' => $cohorte->id,
                'marinUuid' => $marin->uuid
            ],
            'evenementsAttendus' => [EventTypeManager::ATTRIBUER_COHORTE_MARIN],
            'assertionsPersonnalisees' => [
                'evenement' => [
                    'detail_cohorte_libelle' => '22.1',
                    'object_class' => 'Cohorte'
                ],
                'integration' => [
                    'stored_event_count' => 1,
                    'fcm_marin_count' => 1,
                    'cohorte_id_match' => true
                ]
            ]
        ];
    }

    private static function preparerCasMentorCohorte(Marin $marin): array
    {
        $mentor = Marin::factory()->create(['nom' => 'Commandant Florian']);
        $cohorte = Cohorte::factory()->create(['libelle_court' => '22.1']);
        
        return [
            'setup' => [
                'mentor' => $mentor, 
                'cohorte' => $cohorte,
                'record' => $marin
            ],
            'data' => [
                'mentor_id' => $mentor->id,
                'cohorte_id' => $cohorte->id,
                'marinUuid' => $marin->uuid
            ],
            'evenementsAttendus' => [
                EventTypeManager::ATTRIBUER_COHORTE_MARIN,
                EventTypeManager::ATTRIBUER_MENTOR_MARIN,
            ],
            'assertionsPersonnalisees' => [
                'evenement' => [
                    'detail_mentor_nom' => 'Commandant Florian',
                    'detail_cohorte_libelle' => '22.1',
                    'event_type_count' => 2
                ],
                'integration' => [
                    'stored_event_count' => 2,
                    'fcm_marin_count' => 1,
                    'mentor_id_match' => true,
                    'cohorte_id_match' => true
                ]
            ]
        ];
    }

    private static function preparerCasCreationParcours(): array
    {
        $parcours = self::creerParcoursAvecSeeder();
        
        return [
            'setup' => [
                'record' => $parcours,
            ],
            'data' => [
                'libelle_court' => $parcours->libelle_court,
                'libelle_long' => $parcours->libelle_long,
                'version' => '1',
                'date_debut' => Carbon::today()->toDateString(),
                'marinUuid' => ''
            ],
            'evenementsAttendus' => [EventTypeManager::CREATION_PARCOURS_SERIALISE],
            'assertionsPersonnalisees' => [
                'evenement' => [
                    'detail_parcours_nom' => $parcours->libelle_court,
                    'object_class' => 'ParcoursSerialise'
                ],
                'integration' => [
                    'stored_event_count' => 1,
                    'fcm_marin_count' => 0,
                    'parcours_creation_match' => true
                ]
            ]
        ];
    }

    private static function preparerCasAttributionParcours(Marin $marin): array
    {
        $parcours = self::creerParcoursAvecSeeder();
        $fcmMarin = FcmMarin::factory()->create(['rh_marin_id' => $marin->id]);
        
        return [
            'setup' => [
                'parcours' => $parcours,
                'fcmMarin' => $fcmMarin,
                'record' => $marin
            ],
            'data' => [
                'parcours_id' => $parcours->id,
                'fcm_marin_id' => $fcmMarin->id,
                'marinUuid' => $marin->uuid
            ],
            'evenementsAttendus' => [EventTypeManager::ATTRIBUER_PARCOURS_SERIALISE_MARIN],
            'assertionsPersonnalisees' => [
                'evenement' => [
                    'detail_parcours_nom' => $parcours->libelle_court,
                    'detail_marin_nom' => $marin->nom,
                    'object_class' => 'FcmMarin'
                ],
                'integration' => [
                    'stored_event_count' => 1,
                    'fcm_marin_count' => 1,
                    'parcours_attribution_match' => true
                ]
            ]
        ];
    }

    private static function preparerCasErreur(Marin $marin): array
    {
        return [
            'setup' => ['record' => $marin],
            'data' => [
                'cle_invalide' => 'valeur_invalide',
                'marinUuid' => $marin->uuid
            ],
            'evenementsAttendus' => [],
            'assertionsPersonnalisees' => [
                'integration' => [
                    'stored_event_count' => 0,
                    'fcm_marin_count' => 0
                ]
            ]
        ];
    }

    private static function preparerCasDefault(Marin $marin): array
    {
        return [
            'setup' => ['record' => $marin],
            'data' => ['marinUuid' => $marin->uuid],
            'evenementsAttendus' => [],
            'assertionsPersonnalisees' => []
        ];
    }

    /**
     * Méthode helper réutilisable pour créer un parcours avec seeder
     */
    private static function creerParcoursAvecSeeder(): Parcours
    {
        $seeder = new TestParcoursSeeder();
        $seeder->run();
        
        return Parcours::where('libelle_court', 'like', '%PAR%')->first();
    }
}


