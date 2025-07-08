<?php


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\FcmCommun\Services\EventTriggerService;
use Modules\FcmCommun\Services\EventDataService;
use Modules\FcmCommun\Services\EventBuilderService;
use Modules\FcmCommun\Services\EventTypeManager;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCentral\Models\StoredEvent;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(RefreshDatabase::class);
uses(TestCase::class);

pest()->group("EventTriggerService Tests ");

beforeEach(function () {
    // Créer un utilisateur authentifié
    $user = \Modules\RH\Models\User::factory()->create([
        'uuid' => 'user-uuid-123'
    ]);
    $this->actingAs($user);
    
    // Créer un marin de base
    $this->marin = Marin::factory()->create([
        'nom' => 'Test',
        'prenom' => 'Marin',
        'uuid' => 'marin-uuid-123'
    ]);
});

// ========================================
// TESTS SERVICES UNITAIRES
// ========================================

it('peut tester EventTypeManager getAllEventTypes', function() {
    // Etant donné
    $eventTypeManager = new EventTypeManager();
    
    // Quand
    $eventTypes = $eventTypeManager->getAllEventTypes($this->marin);
    
    // Alors
    expect($eventTypes)->toBeArray();
    expect($eventTypes)->toHaveKey(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
    expect($eventTypes)->toHaveKey(EventTypeManager::ATTRIBUER_COHORTE_MARIN);
    expect($eventTypes)->toHaveKey(EventTypeManager::MARIN_EN_FCM);
    expect($eventTypes)->toHaveKey(EventTypeManager::MARIN_PAS_FCM);
    expect($eventTypes)->toHaveKey(EventTypeManager::CREATION_PARCOURS_SERIALISE);
    expect($eventTypes)->toHaveKey(EventTypeManager::ATTRIBUER_PARCOURS_SERIALISE_MARIN);
    expect($eventTypes[EventTypeManager::ATTRIBUER_MENTOR_MARIN])
        ->toHaveKeys(['event_type', 'object_class', 'listener', 'tache']);
    expect($eventTypes[EventTypeManager::ATTRIBUER_MENTOR_MARIN]['tache'])
        ->toContain($this->marin->nom);
});

it('peut tester EventTypeManager getEventTypeDetails', function() {
    // Etant donné
    $eventTypeManager = new EventTypeManager();
    
    // Quand
    $details = $eventTypeManager->getEventTypeDetails(EventTypeManager::ATTRIBUER_MENTOR_MARIN, $this->marin);
    
    // Alors
    expect($details['event_type'])->toBe(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
    expect($details['object_class'])->toBe('FcmMarin');
    expect($details['listener'])->toContain('MentorMarinListener');
    expect($details['tache'])->toContain($this->marin->nom);
});





// ========================================
// TESTS EventBuilderService
// ========================================

it('peut tester EventBuilderService addEventData', function() {
    // Etant donné
    $eventTypeManager = new EventTypeManager();
    $eventBuilder = new EventBuilderService($eventTypeManager);
    
    $eventTypes = [];
    $objectClass = [];
    $objectUuid = [];
    $details = [];
    
    // Quand
    $eventBuilder->addEventData(
        EventTypeManager::ATTRIBUER_MENTOR_MARIN,
        $eventTypes,
        $objectClass,
        $objectUuid,
        $details,
        'mentor-uuid-123',
        ['mentor_nom' => 'Test Mentor'],
        $this->marin,
        'fcmcentral_'
    );
    
    // Alors
    expect($eventTypes)->toContain(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
    expect($objectClass[EventTypeManager::ATTRIBUER_MENTOR_MARIN])->toBe('FcmMarin');
    expect($objectUuid[EventTypeManager::ATTRIBUER_MENTOR_MARIN])->toBe('mentor-uuid-123');
    expect($details)->toHaveKey(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
    expect($details[EventTypeManager::ATTRIBUER_MENTOR_MARIN]['mentor_nom'])->toBe('Test Mentor');
});

it('peut tester EventBuilderService avec plusieurs événements', function() {
    // Etant donné
    $eventTypeManager = new EventTypeManager();
    $eventBuilder = new EventBuilderService($eventTypeManager);
    
    $eventTypes = [];
    $objectClass = [];
    $objectUuid = [];
    $details = [];
    
    // Quand - Ajouter mentor
    $eventBuilder->addEventData(
        EventTypeManager::ATTRIBUER_MENTOR_MARIN,
        $eventTypes,
        $objectClass,
        $objectUuid,
        $details,
        'mentor-uuid-123',
        ['mentor_nom' => 'Test Mentor'],
        $this->marin,
        'fcmcentral_'
    );
    
    // Quand - Ajouter cohorte
    $eventBuilder->addEventData(
        EventTypeManager::ATTRIBUER_COHORTE_MARIN,
        $eventTypes,
        $objectClass,
        $objectUuid,
        $details,
        'cohorte-uuid-456',
        ['cohorte_nom' => 'Test Cohorte'],
        $this->marin,
        'fcmcentral_'
    );
    
    // Alors
    expect($eventTypes)->toHaveCount(2);
    expect($eventTypes)->toContain(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
    expect($eventTypes)->toContain(EventTypeManager::ATTRIBUER_COHORTE_MARIN);
    expect($objectClass)->toHaveKeys([
        EventTypeManager::ATTRIBUER_MENTOR_MARIN,
        EventTypeManager::ATTRIBUER_COHORTE_MARIN
    ]);
});

// ========================================
// TESTS EventDataService
// ========================================

it('peut tester EventDataService buildEventData pour mentor', function() {
    // Etant donné
    $mentor = Marin::factory()->create(['uuid' => 'mentor-uuid-123']);
    
    $eventBuilder = new EventBuilderService(new EventTypeManager());
    $eventDataService = new EventDataService($eventBuilder, 'fcmcentral_');
    
    $data = [
        'mentor_id' => $mentor->id,
        'marinUuid' => $this->marin->uuid
    ];
    
    // Quand
    $eventData = $eventDataService->buildEventData($data, $this->marin);
    
    // Alors
    expect($eventData['eventTypes'])->toContain(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
    expect($eventData['marinUuid'])->toBe($this->marin->uuid);
    expect($eventData['details'])->toHaveKey(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
    expect($eventData['objectClass'][EventTypeManager::ATTRIBUER_MENTOR_MARIN])->toBe('FcmMarin');
    expect($eventData['objectUuid'])->toHaveKey(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
});

it('peut tester EventDataService buildEventData pour cohorte', function() {
    // Etant donné
    $cohorte = Cohorte::factory()->create(['libelle_court' => 'Cohorte Test']);
    
    $eventBuilder = new EventBuilderService(new EventTypeManager());
    $eventDataService = new EventDataService($eventBuilder, 'fcmcentral_');
    
    $data = [
        'cohorte_id' => $cohorte->id,
        'marinUuid' => $this->marin->uuid
    ];
    
    // Quand
    $eventData = $eventDataService->buildEventData($data, $this->marin);
    
    // Alors
    expect($eventData['eventTypes'])->toContain(EventTypeManager::ATTRIBUER_COHORTE_MARIN);
    expect($eventData['marinUuid'])->toBe($this->marin->uuid);
    expect($eventData['details'])->toHaveKey(EventTypeManager::ATTRIBUER_COHORTE_MARIN);
    expect($eventData['objectClass'][EventTypeManager::ATTRIBUER_COHORTE_MARIN])->toBe('Cohorte');
});

it('peut tester EventDataService buildEventData pour mentor ET cohorte', function() {
    // Etant donné
    $mentor = Marin::factory()->create(['uuid' => 'mentor-uuid-123']);
    $cohorte = Cohorte::factory()->create(['libelle_court' => 'Cohorte Test']);
    
    $eventBuilder = new EventBuilderService(new EventTypeManager());
    $eventDataService = new EventDataService($eventBuilder, 'fcmcentral_');
    
    $data = [
        'mentor_id' => $mentor->id,
        'cohorte_id' => $cohorte->id,
        'marinUuid' => $this->marin->uuid
    ];
    
    // Quand
    $eventData = $eventDataService->buildEventData($data, $this->marin);
    
    // Alors
    expect($eventData['eventTypes'])->toHaveCount(2);
    expect($eventData['eventTypes'])->toContain(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
    expect($eventData['eventTypes'])->toContain(EventTypeManager::ATTRIBUER_COHORTE_MARIN);
    expect($eventData['details'])->toHaveKeys([
        EventTypeManager::ATTRIBUER_MENTOR_MARIN,
        EventTypeManager::ATTRIBUER_COHORTE_MARIN
    ]);
});

it('peut tester EventDataService avec données vides', function() {
    // Etant donné
    $eventBuilder = new EventBuilderService(new EventTypeManager());
    $eventDataService = new EventDataService($eventBuilder, 'fcmcentral_');
    
    $data = ['marinUuid' => $this->marin->uuid];
    
    // Quand
    $eventData = $eventDataService->buildEventData($data, $this->marin);
    
    // Alors
    expect($eventData['eventTypes'])->toBeEmpty();
    expect($eventData['marinUuid'])->toBe($this->marin->uuid);
    expect($eventData['details'])->toBeEmpty();
    expect($eventData['objectClass'])->toBeEmpty();
    expect($eventData['objectUuid'])->toBeEmpty();
});




// ========================================
// ========================================

// TESTS INTÉGRATION AVEC VRAIES DONNÉES SERVICES

// ========================================
// ========================================



it('simulation MENTOR via services', function() {
    // Etant donné
    $mentor = Marin::factory()->create(['nom' => 'Capitaine Mentor']);
    
    $eventBuilder = new EventBuilderService(new EventTypeManager());
    $eventDataService = new EventDataService($eventBuilder, 'fcmcentral_');
    
    $data = [
        'mentor_id' => $mentor->id,
        'marinUuid' => $this->marin->uuid
    ];
    
    // Vérifier état initial
    expect(FcmMarin::where('rh_marin_id', $this->marin->id)->exists())->toBeFalse();
    
    // Quand - Étape 1: Construire les données d'événement
    $eventData = $eventDataService->buildEventData($data, $this->marin);

      // Alors
      expect($eventData['eventTypes'])->toContain(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
      expect($eventData['marinUuid'])->toBe($this->marin->uuid);
      expect($eventData['details'])->toHaveKey(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
      expect($eventData['objectClass'][EventTypeManager::ATTRIBUER_MENTOR_MARIN])->toBe('FcmMarin');
      expect($eventData['objectUuid'])->toHaveKey(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
    
    // Étape 2: Créer StoredEvent
    $eventType= $eventData['eventTypes'][0];

    $storedEvent = StoredEvent::create([
        'marin_uuid' => $eventData['marinUuid'],
        'user_uuid' => 'user-uuid-123',
        'event_type' => $eventType,
        'object_class' => $eventData['objectClass'][$eventType],
        'object_uuid' => $eventData['objectUuid'][$eventType],
        'detail' => $eventData['details'],
        'data' => $data,
        
    ]);
    
    // Étape 3: Simuler l'action du listener
    $fcmMarin = FcmMarin::firstOrCreate(
        ['rh_marin_id' => $this->marin->id],
        ['mentor_id' => $mentor->id]
    );
    
   
    
    // Alors - Vérifications multiples
    expect($storedEvent->event_type)->toContain(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
    expect($fcmMarin->mentor_id)->toBe($mentor->id);
  
    
    // Vérifier en base
    $this->assertDatabaseHas('fcmcentral_fcm_marins', [
        'rh_marin_id' => $this->marin->id,
        'mentor_id' => $mentor->id
    ]);
    
    $this->assertDatabaseHas('fcmcentral_stored_events', [
        'marin_uuid' => $this->marin->uuid,
        
    ]);
});



// ========================================
// TESTS INTÉGRATION AVEC VRAIES DONNÉES COHORTE
// ========================================

it('simulation COHORTE via Services', function() {
    // Etant donné
    $cohorte = Cohorte::factory()->create(['libelle_court' => 'Cohorte Test 2025']);
    
    $eventBuilder = new EventBuilderService(new EventTypeManager());
    $eventDataService = new EventDataService($eventBuilder, 'fcmcentral_');
    
    $data = [
        'cohorte_id' => $cohorte->id,
        'marinUuid' => $this->marin->uuid
    ];
    
    // Vérifier état initial
    expect(FcmMarin::where('rh_marin_id', $this->marin->id)->exists())->toBeFalse();
    
    // Quand - Étape 1: Construire les données d'événement
    $eventData = $eventDataService->buildEventData($data, $this->marin);

    
    
    // Étape 2: Créer StoredEvent
    $eventType = $eventData['eventTypes'][0]; // 'attribuer_cohorte_marin'
    
    $storedEvent = StoredEvent::create([
        'marin_uuid' => $eventData['marinUuid'],
        'user_uuid' => 'user-uuid-123',
        'event_type' => $eventType,
        'object_class' => $eventData['objectClass'][$eventType],
        'object_uuid' => $eventData['objectUuid'][$eventType],
        'detail' => $eventData['details'][$eventType], // Détail spécifique à la cohorte
        'data' => $data,
        
    ]);
    
    // Étape 3: Simuler l'action du listener CohorteMarinListener
    $fcmMarin = FcmMarin::firstOrCreate(
        ['rh_marin_id' => $this->marin->id],
        ['cohorte_id' => $cohorte->id]
    );
    
   ;
    
    // Alors - Vérifications multiples
    expect($storedEvent->event_type)->toBe(EventTypeManager::ATTRIBUER_COHORTE_MARIN);
    expect($fcmMarin->cohorte_id)->toBe($cohorte->id);
   
    
    
    //expect($storedEvent->detail)->toHaveKey('cohorte_nom');
    expect($storedEvent->event_type)->toBe(EventTypeManager::ATTRIBUER_COHORTE_MARIN);
    expect($storedEvent->marin_uuid)->toBe($this->marin->uuid);
    expect($storedEvent->user_uuid)->toBe('user-uuid-123');
    expect($storedEvent->object_class)->toBe('Cohorte');
    expect($storedEvent->object_uuid)->not()->toBeNull();
    // Vérifier les détails de l'événement cohorte
    expect($storedEvent->detail)->toBeArray();
    expect($storedEvent->detail['tache'])->toContain('Attribuer une Cohorte');
    expect($storedEvent->detail['prefix'])->toBe('fcmcentral_');
    expect($storedEvent->detail['cohorte_libelle_court'])->toBe('Cohorte Test 2025');
    
    // Vérifier en base
    $this->assertDatabaseHas('fcmcentral_fcm_marins', [
        'rh_marin_id' => $this->marin->id,
        'cohorte_id' => $cohorte->id
    ]);
    
    $this->assertDatabaseHas('fcmcentral_stored_events', [
        'marin_uuid' => $this->marin->uuid,
        'event_type' => EventTypeManager::ATTRIBUER_COHORTE_MARIN,
        
    ]);
});

// ========================================
// TESTS INTÉGRATION AVEC VRAIES DONNÉES COHORTE ET MENTOR
// ========================================

it('simulation MENTOR et COHORTE via Services', function() {
    // Etant donné
    $mentor = Marin::factory()->create(['nom' => 'Amiral Mentor']);
    $cohorte = Cohorte::factory()->create(['libelle_court' => 'Cohorte Multi 2025']);
    
    $eventBuilder = new EventBuilderService(new EventTypeManager());
    $eventDataService = new EventDataService($eventBuilder, 'fcmcentral_');
    
    $data = [
        'mentor_id' => $mentor->id,
        'cohorte_id' => $cohorte->id,
        'marinUuid' => $this->marin->uuid
    ];
    
    // Vérifier état initial
    expect(FcmMarin::where('rh_marin_id', $this->marin->id)->exists())->toBeFalse();
    
    // Quand - Étape 1: Construire les données d'événement
    $eventData = $eventDataService->buildEventData($data, $this->marin);
    
    // Étape 2: Créer StoredEvent pour chaque type d'événement
    $eventUuid = Str::uuid();
    $storedEvents = [];
    
    foreach ($eventData['eventTypes'] as $eventType) {
        $storedEvents[] = StoredEvent::create([
            'event_uuid' => $eventUuid,
            'marin_uuid' => $eventData['marinUuid'],
            'user_uuid' => 'user-uuid-123',
            'event_type' => $eventType,
            'object_class' => $eventData['objectClass'][$eventType],
            'object_uuid' => $eventData['objectUuid'][$eventType],
            'detail' => $eventData['details'][$eventType],
            'data' => $data,
            
        ]);
    }
    
    // Étape 3: Simuler l'action des listeners
    $fcmMarin = FcmMarin::firstOrCreate(
        ['rh_marin_id' => $this->marin->id],
        [
            'mentor_id' => $mentor->id,
            'cohorte_id' => $cohorte->id
        ]
    );
    
    // Étape 4: Marquer tous les événements comme traités
  
    
    // Alors - Vérifications multiples
    expect($storedEvents)->toHaveCount(2);
    
    // Vérifier événement mentor
    $mentorEvent = collect($storedEvents)->firstWhere('event_type', EventTypeManager::ATTRIBUER_MENTOR_MARIN);
    expect($mentorEvent)->not()->toBeNull();
    expect($mentorEvent->event_type)->toBe(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
    expect($mentorEvent->marin_uuid)->toBe($this->marin->uuid);
    expect($mentorEvent->user_uuid)->toBe('user-uuid-123');
    expect($mentorEvent->object_class)->toBe('FcmMarin');
    expect($mentorEvent->object_uuid)->not()->toBeNull();
    // Vérifier les détails de l'événement cohorte
    expect($mentorEvent->detail)->toBeArray();
    expect($mentorEvent->detail['tache'])->toContain('Attribuer un mentor');
    expect($mentorEvent->detail['prefix'])->toBe('fcmcentral_');
    expect($mentorEvent->detail['mentor_nom'])->toBe('Amiral Mentor');

    // Vérifier événement cohorte
    $cohorteEvent = collect($storedEvents)->firstWhere('event_type', EventTypeManager::ATTRIBUER_COHORTE_MARIN);
    expect($cohorteEvent)->not()->toBeNull();
    expect($cohorteEvent->event_type)->toBe(EventTypeManager::ATTRIBUER_COHORTE_MARIN);
    expect($cohorteEvent->marin_uuid)->toBe($this->marin->uuid);
    expect($cohorteEvent->user_uuid)->toBe('user-uuid-123');
    expect($cohorteEvent->object_class)->toBe('Cohorte');
    expect($cohorteEvent->object_uuid)->not()->toBeNull();
    // Vérifier les détails de l'événement cohorte
    expect($cohorteEvent->detail)->toBeArray();
    expect($cohorteEvent->detail['tache'])->toContain('Attribuer une Cohorte');
    expect($cohorteEvent->detail['prefix'])->toBe('fcmcentral_');
    expect($cohorteEvent->detail['cohorte_libelle_court'])->toBe('Cohorte Multi 2025');
    ;
    
    // Vérifier FcmMarin
    expect($fcmMarin->mentor_id)->toBe($mentor->id);
    expect($fcmMarin->cohorte_id)->toBe($cohorte->id);
    
    // Vérifier en base
    $this->assertDatabaseHas('fcmcentral_fcm_marins', [
        'rh_marin_id' => $this->marin->id,
        'mentor_id' => $mentor->id,
        'cohorte_id' => $cohorte->id
    ]);
    
    $this->assertDatabaseHas('fcmcentral_stored_events', [
        'event_uuid' => $eventUuid,
        'event_type' => EventTypeManager::ATTRIBUER_MENTOR_MARIN,
        
    ]);
    
    $this->assertDatabaseHas('fcmcentral_stored_events', [
        'event_uuid' => $eventUuid,
        'event_type' => EventTypeManager::ATTRIBUER_COHORTE_MARIN,
        
    ]);
});


// ========================================
// TESTS INTÉGRATION AVEC VRAIES DONNÉES FCM
// ========================================




// ========================================
// TESTS GESTION D'ERREURS
// ========================================



it('peut traiter des événements multiples complexes', function() {
    // Etant donné
    $mentor = Marin::factory()->create();
    $cohorte = Cohorte::factory()->create();
    
    $eventBuilder = new EventBuilderService(new EventTypeManager());
    $eventDataService = new EventDataService($eventBuilder, 'fcmcentral_');
    
    $data = [
        'mentor_id' => $mentor->id,
        'cohorte_id' => $cohorte->id,
        'fcm' => ['en_fcm' => 'true'],
        'selectedItems' => ['init' => ['item1']],
        'eventName' => 'valid',
        'objectUuid' => 'test-uuid',
        'marinUuid' => $this->marin->uuid
    ];
    
    // Quand
    $eventData = $eventDataService->buildEventData($data, $this->marin);
    
    // Alors
    expect($eventData['eventTypes'])->toHaveCount(4);
    expect($eventData['eventTypes'])->toContain(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
    expect($eventData['eventTypes'])->toContain(EventTypeManager::ATTRIBUER_COHORTE_MARIN);
    //expect($eventData['eventTypes'])->toContain(EventTypeManager::MARIN_EN_FCM);
    //expect($eventData['eventTypes'])->toContain(EventTypeManager::VALID_ELMT_MARIN_PARCOURS);
});

// ========================================
// TESTS PERFORMANCE ET EDGE CASES
// ========================================

it('peut traiter de grandes quantités de données', function() {
    // Etant donné
    $eventBuilder = new EventBuilderService(new EventTypeManager());
    $eventDataService = new EventDataService($eventBuilder, 'fcmcentral_');
    
    $mentors = Marin::factory()->count(10)->create();
    $cohortes = Cohorte::factory()->count(5)->create();
    
    // Quand - Traiter plusieurs événements
    $allEventData = [];
    foreach ($mentors as $index => $mentor) {
        $data = [
            'mentor_id' => $mentor->id,
            'cohorte_id' => $cohortes[$index % 5]->id,
            'marinUuid' => $this->marin->uuid
        ];
        
        $allEventData[] = $eventDataService->buildEventData($data, $this->marin);
    }
    
    // Alors
    expect($allEventData)->toHaveCount(10);
    foreach ($allEventData as $eventData) {
        expect($eventData['eventTypes'])->toHaveCount(2);
        expect($eventData['eventTypes'])->toContain(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
        expect($eventData['eventTypes'])->toContain(EventTypeManager::ATTRIBUER_COHORTE_MARIN);
    }
});
