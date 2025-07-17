<?php


use Modules\FcmCommun\Services\EventTypeManager;
use Modules\FcmCentral\Models\StoredEvent;
use Modules\FcmCentral\Models\FcmMarin;

class EventTestAssertions
{
    /**
     * Exécute les assertions personnalisées sur l'événement
     */
    public static function executerAssertionsEvenement($evenement, array $modeles, array $assertions): void
    {
        if (isset($assertions['detail_mentor_nom'])) {
            expect($evenement->detail[EventTypeManager::ATTRIBUER_MENTOR_MARIN]['mentor_nom'])
                ->toBe($assertions['detail_mentor_nom']);
        }
        
        if (isset($assertions['detail_cohorte_libelle'])) {
            expect($evenement->detail[EventTypeManager::ATTRIBUER_COHORTE_MARIN]['cohorte_libelle_court'])
                ->toBe($assertions['detail_cohorte_libelle']);
        }
        
        if (isset($assertions['object_class'])) {
            $typeEvenement = array_keys($evenement->object_class)[0];
            expect($evenement->object_class[$typeEvenement])
                ->toBe($assertions['object_class']);
        }
        
        if (isset($assertions['object_uuid'])) {
            $typeEvenement = array_keys($evenement->object_uuid)[0];
            $modeleNom = explode('.', $assertions['object_uuid'])[0];
            expect($evenement->object_uuid[$typeEvenement])
                ->toBe($modeles[$modeleNom]->uuid);
        }
        
        if (isset($assertions['event_type_count'])) {
            expect($evenement->event_type)->toHaveCount($assertions['event_type_count']);
        }
    }

    /**
     * Exécute les assertions personnalisées sur l'intégration
     */
    public static function executerAssertionsIntegration(array $modeles, $marin, array $assertions): void
    {
        if (isset($assertions['stored_event_count'])) {
            expect(StoredEvent::count())->toBe($assertions['stored_event_count']);
        }
        
        if (isset($assertions['fcm_marin_count'])) {
            expect(FcmMarin::count())->toBe($assertions['fcm_marin_count']);
            
            if ($assertions['fcm_marin_count'] > 0) {
                $fcmMarin = FcmMarin::first();
                
                if (isset($assertions['mentor_id_match']) && $assertions['mentor_id_match']) {
                    expect($fcmMarin->mentor_id)->toBe($modeles['mentor']->id);
                }
                
                if (isset($assertions['cohorte_id_match']) && $assertions['cohorte_id_match']) {
                    expect($fcmMarin->cohorte_id)->toBe($modeles['cohorte']->id);
                }
                
                if (isset($assertions['rh_marin_id_match']) && $assertions['rh_marin_id_match']) {
                    expect($fcmMarin->rh_marin_id)->toBe($marin->id);
                }
            }
        }
    }

    /**
     * Vérifie les événements stockés selon le cas de test
     */
    public static function verifierEvenementsStockes(string $casTest, array $preparation): void
    {
        if (!empty($preparation['evenementsAttendus'])) {
            if (isset($preparation['assertionsPersonnalisees']['integration']['stored_event_count'])) {
                $expectedCount = $preparation['assertionsPersonnalisees']['integration']['stored_event_count'];
                expect(StoredEvent::count())->toBe($expectedCount);
                
                if ($expectedCount > 0) {
                    $evenementStocke = StoredEvent::first();
                    expect($evenementStocke->event_type)
                        ->toBe($preparation['evenementsAttendus'][0]);
                     
                    // Pour MENTOR_COHORTE, vérifier le deuxième événement
                    if ($casTest === 'MENTOR_COHORTE' && $expectedCount > 1) {
                        $deuxiemeEvenement = StoredEvent::skip(1)->first();
                        expect($deuxiemeEvenement->event_type)
                            ->toBe($preparation['evenementsAttendus'][1]);
                    }
                }
            } else {
                // Comportement par défaut
                expect(StoredEvent::count())->toBe(1);
                $evenementStocke = StoredEvent::first();
                expect($evenementStocke->event_type)->toBe($preparation['evenementsAttendus'][0]);
            }
        }
    }
}


