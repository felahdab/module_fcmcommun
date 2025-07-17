<?php
 
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\FcmCommun\Services\EventTriggerService;
use Modules\FcmCommun\Services\EventTypeManager;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\StoredEvent;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\FcmCentral\Database\Seeders\Tests\TestParcoursSeeder;

uses(RefreshDatabase::class);
uses(TestCase::class);
 
pest()->group("EventTriggerService Tests");
 
// ========================================
// ⚙️ INIT - CONFIGURATION INITIALE
// ========================================
 
beforeEach(function () {
    // Créer un utilisateur authentifié
    $this->user = \Modules\RH\Models\User::factory()->create([
        'uuid' => 'user-uuid-123'
    ]);
    // Se faire passer pour cet user
    $this->actingAs($this->user);
    expect(auth()->check())->toBeTrue();
    expect(auth()->user()->uuid)->toBe('user-uuid-123');
    
    // Créer un marin de base
    $this->marin = Marin::factory()->create([
        'nom' => 'Test',
        'prenom' => 'Marin',
        'uuid' => 'marin-uuid-123'
    ]);
 
    $this->prefix = 'fcmcentral_';
});
 
// ========================================
// ⚙️ DATASET
// ========================================
 
dataset('casTestEvenements', [
    'MENTOR',
    'COHORTE',
    'MENTOR_COHORTE',
    'CREATION_PARCOURS_SERIALISE',
   // 'ATTRIBUER_PARCOURS_SERIALISE_MARIN',

    'ERREUR',
]);
 
// ========================================
// ⚙️ HELPER FUNCTIONS
// ========================================
 
/**
 * 🎯 Prépare les données selon le type de test
 */
function preparerDonneesTest($casTest, $marin) {
    switch ($casTest) {
        case 'MENTOR':
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
                'evenementsAttendus' => [
                    EventTypeManager::ATTRIBUER_MENTOR_MARIN
                ],
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
            break; 
        case 'COHORTE':
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
                'evenementsAttendus' => [
                    EventTypeManager::ATTRIBUER_COHORTE_MARIN
                ],
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
            break;    
        case 'MENTOR_COHORTE':
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
                        'stored_event_count' => 2, // 2 événements attendus
                        'fcm_marin_count' => 1,    // Mais 1 seul FcmMarin
                        'mentor_id_match' => true,
                        'cohorte_id_match' => true
                    ]
                ]
            ];
          break;  
        case 'ERREUR':   
            return [
                'setup' => [
                    'record' => $marin
                ],
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
            break; 
            case 'CREATION_PARCOURS_SERIALISE':
                // ✅ Utiliser un parcours du seeder DeasmMasterSeeder
                // $parcours = Parcours::factory()->create([
                //     'libelle_court' => 'DEASM Master Attribution',
                //     'libelle_long' => 'Parcours pour test d\'attribution'
                // ]);


                    //$this->seed(DeasmMasterSeeder::class);
                    //artisan::call('db:seed',['--class'=>'DeasmMasterSeeder']);
                $seeder = new TestParcoursSeeder();
                $seeder->run();

                       
                    // Ne pas utiliser DB car ca cree bug
                // $parcours = DB::table( 'fcmcentral_parcours')
                // Utiliser model pour que ca marche
                $parcours = Parcours::
                where('libelle_court', 'like', '%PAR%')
                ->first();
                   
            
                return [
                    'setup' => [
                      
                        'record' => $parcours,
                    ],
                    'data' => [
                        //'parcours_id' => $parcours->id,
                        // champ obligatoire poru declencher Listener
                        //'parcours_id' => $parcours->id,
                        'libelle_court' => $parcours->libelle_court,
                        'libelle_long' => $parcours->libelle_long,
                        'version' => '1',
                        'date_debut' =>Carbon::today()->toDateString(),
                        //
                        'marinUuid' => ''
                    ],
                    'evenementsAttendus' => [
                        EventTypeManager::CREATION_PARCOURS_SERIALISE
                    ],
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
                break; 
            case 'ATTRIBUER_PARCOURS_SERIALISE_MARIN':
                // ✅ NOUVEAU CAS - Attribution d'un parcours à un marin existant
                
                $parcours = DB::table( 'fcmcentral_parcours_serialises')
                ->where('libelle_court', 'like', '%DEASM%')
            
                    ->first();
                    
                if (!$parcours) {
                    $parcours = Parcours::factory()->create([
                        'libelle_court' => 'DEASM Master Attribution',
                        'libelle_long' => 'Parcours pour test d\'attribution'
                    ]);
                }
     
                // Créer un FcmMarin existant pour l'attribution
                $fcmMarin = FcmMarin::factory()->create([
                    'rh_marin_id' => $marin->id
                ]);
                
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
                    'evenementsAttendus' => [
                        EventTypeManager::ATTRIBUER_PARCOURS_SERIALISE_MARIN
                    ],
                    'assertionsPersonnalisees' => [
                        'evenement' => [
                            'detail_parcours_nom' => $parcours->nom,
                            'detail_marin_nom' => $marin->nom,
                            'object_class' => 'FcmMarin'
                        ],
                        'integration' => [
                            'stored_event_count' => 1,
                            'fcm_marin_count' => 1, // Déjà existant
                            'parcours_attribution_match' => true
                        ]
                    ]
                ];
                break; 
            
        default:
            return [
                'setup' => ['record' => $marin],
                'data' => ['marinUuid' => $marin->uuid],
                'evenementsAttendus' => [],
                'assertionsPersonnalisees' => []
            ];
            break; 
    }
}
 
/**
 * 🔍 Vérifie les logs selon le type de test - VERSION CORRIGÉE
 */
function verifierLogs($logSpy, $casTest, $data, $record, $prefix, $marinUuid) {
    // Logs communs à tous les tests
    $logSpy->shouldHaveReceived('info')
        ->with('Prefixe:', ['prefix' => $prefix])
        ->once();
 
    $logSpy->shouldHaveReceived('info')
        ->with('EventTrigger appele', ['data' => $data, 'record' => $record])
        ->once();
 
    $logSpy->shouldHaveReceived('info')
        ->with('buildEventData appele', ['data' => $data, 'record' => $record])
        ->once();
    
    // Logs spécifiques selon le type
    // Se trouve dans FcmCommun/Services/EventDataService
    switch ($casTest) {
        case 'MENTOR':
            $logSpy->shouldHaveReceived('info')
                ->with('EventBuilder Data handleMentor genere')
                ->once();
            break;
            
        case 'COHORTE':
            $logSpy->shouldHaveReceived('info')
                ->with('EventBuilder Data handleCohorte genere')
                ->once();
            break;
            
        case 'MENTOR_COHORTE':
            $logSpy->shouldHaveReceived('info')
                ->with('EventBuilder Data handleMentor genere')
                ->once();
            $logSpy->shouldHaveReceived('info')
                ->with('EventBuilder Data handleCohorte genere')
                ->once();
            break;   

            case 'CREATION_PARCOURS_SERIALISE':
                $logSpy->shouldHaveReceived('info')
                    ->with('EventBuilder Data handleCreationParcoursSerialise genere')
                    ->once();
             
            break;    

            case 'ATTRIBUER_PARCOURS_SERIALISE_MARIN':
                    $logSpy->shouldHaveReceived('info')
                        ->with('EventBuilder Data handleParcoursSerialiseMarin genere')
                        ->once();
                 
            break;        
            
        case 'ERREUR':
            // Pour les cas d'erreur, on peut avoir des logs différents
            return;
    }
    
    // Logs suite pour les cas normaux
    if ($casTest !== 'ERREUR') {
        if ($casTest !== 'CREATION_PARCOURS_SERIALISE') {
        $logSpy->shouldHaveReceived('info')
            ->with('Donnees generes', \Mockery::on(function ($context) use ($marinUuid) {
                return isset($context['marinUuid']) && $context['marinUuid'] === $marinUuid;
            }))
            ->once();   
        }else{
            // marinuuid null
            $logSpy->shouldHaveReceived('info')
            ->with('Donnees generes',  \Mockery::type('array'))
            ->once();   
        }

        $logSpy->shouldHaveReceived('info')
            ->with('Event cree', \Mockery::type('array'))
            ->once();
 
        $logSpy->shouldHaveReceived('info')
            ->with('CreateFromUserGeneratedEvent Array cree')
            ->once();
 
        // Logs des listeners spécifiques
        switch ($casTest) {
            case 'MENTOR':
                $logSpy->shouldHaveReceived('info')
                    ->with('MentorMarinListener appele', \Mockery::on(function ($context) use ($marinUuid) {
                        return isset($context['marinUuid']) && $context['marinUuid'] === $marinUuid;
                    }))
                    ->once();
                break;
                
            case 'COHORTE':
                $logSpy->shouldHaveReceived('info')
                    ->with('CohorteMarinListener appele', \Mockery::on(function ($context) use ($marinUuid) {
                        return isset($context['marinUuid']) && $context['marinUuid'] === $marinUuid;
                    }))
                    ->once();
                break;
                
            case 'MENTOR_COHORTE':
                $logSpy->shouldHaveReceived('info')
                    ->with('MentorMarinListener appele', \Mockery::on(function ($context) use ($marinUuid) {
                        return isset($context['marinUuid']) && $context['marinUuid'] === $marinUuid;
                    }))
                    ->once();
                    
                $logSpy->shouldHaveReceived('info')
                    ->with('CohorteMarinListener appele', \Mockery::on(function ($context) use ($marinUuid) {
                        return isset($context['marinUuid']) && $context['marinUuid'] === $marinUuid;
                    }))
                    ->once();
                break;
        }
 
        // Log fcm_marin peut être appelé plusieurs fois selon le cas
        if ($casTest !== 'CREATION_PARCOURS_SERIALISE') {
        $expectedCalls = ($casTest === 'MENTOR_COHORTE') ? 2 : 1;
        
        $logSpy->shouldHaveReceived('info')
            ->with($prefix.'fcm_marin  :', \Mockery::on(function ($context) use ($marinUuid) {
                return isset($context['uuid']) && $context['uuid'] === $marinUuid;
            }))
            ->times($expectedCalls);
    }
}
}
 
/**
 * ✅ Exécute les assertions personnalisées sur l'événement
 */
function executerAssertionsEvenement($evenement, $modeles, $assertions) {
    if (isset($assertions['detail_mentor_nom'])) {
        expect($evenement->detail
        [EventTypeManager::ATTRIBUER_MENTOR_MARIN]['mentor_nom'])
            ->toBe($assertions['detail_mentor_nom']);
    }
    
    if (isset($assertions['detail_cohorte_libelle'])) {
        expect($evenement->detail
        [EventTypeManager::ATTRIBUER_COHORTE_MARIN]['cohorte_libelle_court'])
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
 * ✅ Exécute les assertions personnalisées sur l'intégration
 */
function executerAssertionsIntegration($modeles, $marin, $assertions) {
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
 
// ========================================
// ⚙️ TESTS PRINCIPAUX
// ========================================
 
it('🎯 teste EventTrigger avec Logs', function($casTest) {
    // S'assurer de l'authentification
    $this->actingAs($this->user);
    
    // ===== 🏗️ PRÉPARATION =====
    $preparation = preparerDonneesTest($casTest, $this->marin);
    $setup = $preparation['setup'];
    $data = $preparation['data'];
    $record = $setup['record'];
    
    // ===== 📝 TEST 1: VÉRIFICATION DES LOGS =====
    $logSpy = Log::spy();
    
    EventTriggerService::triggerEvent($data, $record);
  
    verifierLogs($logSpy, $casTest, $data, $record, $this->prefix, $this->marin->uuid);
 
})->with('casTestEvenements');
 
it('🎯 teste EventTrigger avec Data builder', function($casTest) {
 
    
    // ===== 🏗️ PRÉPARATION =====
    $preparation = preparerDonneesTest($casTest, $this->marin);
    $setup = $preparation['setup'];
    $data = $preparation['data'];
    $record = $setup['record'];
    
    // ===== 📡 TEST 2: VÉRIFICATION DE L'ÉVÉNEMENT =====
    Event::fake([UserGeneratedEvent::class]);
    EventTriggerService::triggerEvent($data, $record);
 
    if (empty($preparation['evenementsAttendus'])) {
        Event::assertNotDispatched(UserGeneratedEvent::class);
    } else {
        Event::assertDispatched(UserGeneratedEvent::class, function ($evenement) use ($preparation, $setup) {
            expect($evenement->event_type)->toBeArray();
            expect($evenement->user_uuid)->toBe($this->user->uuid);
            
            // Vérifier chaque type d'événement attendu
            foreach ($preparation['evenementsAttendus'] as $typeEvenementAttendu) {
                expect($evenement->event_type)->toContain($typeEvenementAttendu);
            }
            
            // Assertions personnalisées sur l'événement
            if (isset($preparation['assertionsPersonnalisees']['evenement'])) {
                executerAssertionsEvenement($evenement, $setup, $preparation['assertionsPersonnalisees']['evenement']);
            }
            
            return true;
        });
        
        Event::assertDispatchedTimes(UserGeneratedEvent::class, 1);
    }
 
})->with('casTestEvenements');
 
it('🎯 teste EventTrigger avec Base Event', function($casTest) {
    
    
    // ===== 🏗️ PRÉPARATION =====
    $preparation = preparerDonneesTest($casTest, $this->marin);
    $setup = $preparation['setup'];
    $data = $preparation['data'];
    $record = $setup['record'];
 
    // ===== 💾 TEST 3: VÉRIFICATION DE L'INTÉGRATION =====
    
    // États initiaux
    expect(StoredEvent::count())->toBe(0);
    expect(FcmMarin::count())->toBe(0);
 
    // Exécution réelle
    EventTriggerService::triggerEvent($data, $record);
 
    // ✅ CORRECTION - Assertions d'intégration selon le cas
    if (!empty($preparation['evenementsAttendus'])) {
        // Pour les cas normaux, utiliser les assertions personnalisées
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
 
    // Assertions d'intégration personnalisées
    if (isset($preparation['assertionsPersonnalisees']['integration'])) {
        executerAssertionsIntegration($setup, $this->marin, $preparation['assertionsPersonnalisees']['integration']);
    }
 
})->with('casTestEvenements');
 
// ========================================
// ⚙️ TESTS SPÉCIFIQUES RAPIDES
// ========================================
 
// it('🚀 peut attribuer un mentor à un marin', function() {
    
    
//     $mentor = Marin::factory()->create(['nom' => 'Test Mentor']);
//     $data = ['mentor_id' => $mentor->id, 'marinUuid' => $this->marin->uuid];
    
//     Event::fake([UserGeneratedEvent::class]);
//     EventTriggerService::triggerEvent($data, $this->marin);
    
//     Event::assertDispatched(UserGeneratedEvent::class, function ($event) {
//         expect($event->event_type)->toContain(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
//         return true;
//     });
// });
 
// it('🚀 peut attribuer une cohorte à un marin', function() {
//     $this->actingAs($this->user);
    
//     $cohorte = Cohorte::factory()->create(['libelle_court' => 'Test Cohorte']);
//     $data = ['cohorte_id' => $cohorte->id, 'marinUuid' => $this->marin->uuid];
    
//     Event::fake([UserGeneratedEvent::class]);
//     EventTriggerService::triggerEvent($data, $this->marin);
    
//     Event::assertDispatched(UserGeneratedEvent::class, function ($event) {
//         expect($event->event_type)->toContain(EventTypeManager::ATTRIBUER_COHORTE_MARIN);
//         return true;
//     });
// });
 
// it('🚀 peut attribuer mentor + cohorte simultanément', function() {
//     $this->actingAs($this->user);
    
//     $mentor = Marin::factory()->create(['nom' => 'Commandant Test']);
//     $cohorte = Cohorte::factory()->create(['libelle_court' => 'Formation Test']);
    
//     $data = [
//         'mentor_id' => $mentor->id,
//         'cohorte_id' => $cohorte->id,
//         'marinUuid' => $this->marin->uuid
//     ];
    
//     // États initiaux
//     expect(StoredEvent::count())->toBe(0);
//     expect(FcmMarin::count())->toBe(0);
    
//     // Exécution
//     EventTriggerService::triggerEvent($data, $this->marin);
    
//     // Vérifications
//     expect(StoredEvent::count())->toBe(2); // 2 événements distincts
//     expect(FcmMarin::count())->toBe(1);    // 1 seul enregistrement FcmMarin
    
//     $fcmMarin = FcmMarin::first();
//     expect($fcmMarin->mentor_id)->toBe($mentor->id);
//     expect($fcmMarin->cohorte_id)->toBe($cohorte->id);
//     expect($fcmMarin->rh_marin_id)->toBe($this->marin->id);
// });
 
// it('🚀 gère les cas d\'erreur avec données invalides', function() {
//     $this->actingAs($this->user);
    
//     $data = ['cle_invalide' => 'valeur_invalide', 'marinUuid' => $this->marin->uuid];
    
//     Event::fake([UserGeneratedEvent::class]);
//     EventTriggerService::triggerEvent($data, $this->marin);
    
//     Event::assertNotDispatched(UserGeneratedEvent::class);
    
//     // Vérifier qu'aucun enregistrement n'est créé
//     expect(StoredEvent::count())->toBe(0);
//     expect(FcmMarin::count())->toBe(0);
// });
 
 
