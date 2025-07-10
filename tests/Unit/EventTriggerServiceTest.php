<?php


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\FcmCommun\Services\EventTriggerService;
use Modules\FcmCommun\Services\EventTypeManager;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCentral\Models\StoredEvent;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;


uses(RefreshDatabase::class);
uses(TestCase::class);

pest()->group("EventTriggerService Tests");
 
beforeEach(function () {

    

    // Créer un utilisateur authentifié
    $this->user = \Modules\RH\Models\User::factory()->create([
        'uuid' => 'user-uuid-123'
    ]);
    $this->actingAs($this->user);
    
    // Créer un marin de base
    $this->marin = Marin::factory()->create([
        'nom' => 'Test',
        'prenom' => 'Marin',
        'uuid' => 'marin-uuid-123'
    ]);
 
    $this->prefix = 'fcmcentral_';
});


// ========================================
// TESTS SERVICES UNITAIRES
// ========================================
 
it('simulation Event via MENTOR : déclenchement 3 Actions', function() {
    // Etant donné
    $mentor = Marin::factory()->create(['nom' => 'Capitaine Mentor']); 
    $record = $this->marin;
    
    $data = [
        'mentor_id' => $mentor->id,
        'marinUuid' => $this->marin->uuid  
    ];
    
    //  Fake les événements pour capturer ce qui est déclenché
    Event::fake([
        UserGeneratedEvent::class
    ]);
    
    // Quand
    EventTriggerService::triggerEvent($data, $record);
    
    // Alors - Vérifier que l'événement UserGeneratedEvent a été déclenché
    Event::assertDispatched(UserGeneratedEvent::class, function ($event) use ($mentor) {
        //  Vérifier le préfixe
        expect($event->prefixTable)->toBe($this->prefix);
        
        //  Vérifier les données de l'événement
        expect($event->user_uuid)->toBe($this->user->uuid);
        expect($event->marin_uuid)->toBe($this->marin->uuid);
        expect($event->event_type)->toBeArray();
        expect($event->event_type)->toContain(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
        
        //  Vérifier les détails
        expect($event->detail)->toBeArray();
        expect($event->detail)->toHaveKey(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
        expect($event->detail[EventTypeManager::ATTRIBUER_MENTOR_MARIN])->toHaveKey('mentor_nom');
        expect($event->detail[EventTypeManager::ATTRIBUER_MENTOR_MARIN]['mentor_nom'])->toBe('Capitaine Mentor');
        
        return true;
    });
    
    //  Vérifier qu'un seul UserGeneratedEvent a été déclenché
    Event::assertDispatchedTimes(UserGeneratedEvent::class, 1);
});
 
it('vérifie que UserGeneratedEvent déclenche les 3 listeners', function() {
    // Etant donné
    $mentor = Marin::factory()->create(['nom' => 'Capitaine Mentor']); 
    $record = $this->marin;
    
    $data = [
        'mentor_id' => $mentor->id,
        'marinUuid' => $this->marin->uuid
    ];
    
    // Ne pas fake les événements pour permettre aux listeners de s'exécuter
    // Event::fake(); 
    
    // Vérifier état initial
    expect(StoredEvent::count())->toBe(0);
    
    // Quand
    EventTriggerService::triggerEvent($data, $record);
    
    // Alors - Vérifier que les 3 actions ont été exécutées
    
    // 1. StoreUserGeneratedEventListener - Doit créer un StoredEvent
    expect(StoredEvent::count())->toBeGreaterThan(0);
    
    $storedEvent = StoredEvent::first();
    expect($storedEvent->event_type)->toBe(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
    expect($storedEvent->marin_uuid)->toBe($this->marin->uuid);
    expect($storedEvent->user_uuid)->toBe($this->user->uuid);
    
    // 2. SaveUserGeneratedEventListener - Doit créer/modifier FcmMarin
    expect(FcmMarin::where('rh_marin_id', $this->marin->id)->exists())->toBeTrue();
    
    $fcmMarin = FcmMarin::where('rh_marin_id', $this->marin->id)->first();
    expect($fcmMarin->mentor_id)->toBe($mentor->id);
    
    // 3. TransmitUserGeneratedEventListener - Vérifier la transmission
    // (Si ce listener envoie des données vers une API externe, mocker l'appel)
    // expect(...)->toHaveBeenCalled();
});
 
it('vérifie le prefixe retourné par EventTriggerService', function() {
    // Etant donné
    $mentor = Marin::factory()->create(['nom' => 'Capitaine Mentor']); 
    $record = $this->marin;
    
    $data = [
        'mentor_id' => $mentor->id,
        'marinUuid' => $this->marin->uuid
    ];
    
    // Fake pour capturer l'événement
    Event::fake([UserGeneratedEvent::class]);
    
    // Quand
    EventTriggerService::triggerEvent($data, $record);
    
    // Alors - Vérifier le préfixe
    Event::assertDispatched(UserGeneratedEvent::class, function ($event) {
        expect($event->prefixTable)->toBe($this->prefix);
        return true;
    });
    
    // Vérifier également via la méthode statique
    expect(UserGeneratedEvent::getPrefixTable())->toBe($this->prefix);
});


// ========================================
// VERIFICATION DU PROCESSUS PAR LES LOGS
// ========================================
 
it('vérifie par retour de Log (Method SPY) de EventTrigger', function() {
    // Etant donné
    $mentor = Marin::factory()->create(['nom' => 'Capitaine Mentor']); 
    $record = $this->marin;
    
    $data = [
        'mentor_id' => $mentor->id,
        'marinUuid' => $this->marin->uuid
    ];
 
    // Utiliser spy au lieu de mock
    $logSpy = Log::spy();
    
    // Quand
    EventTriggerService::triggerEvent($data, $record);
    
    // Alors - Log renvoye

    // FcmCommun Services EventTriggerService pour le prefix
    $logSpy->shouldHaveReceived('info')
        ->with('Prefixe:', ['prefix' => $this->prefix])
        ->once();

    // FcmCommun Services EventTriggerService pour le declencher Event    
    $logSpy->shouldHaveReceived('info')
        ->with('EventTrigger appele', ['data' => $data, 'record' => $record])
        ->once();

    // FcmCommun Services EventDataService pour la creation data   
    $logSpy->shouldHaveReceived('info')
        ->with('buildEventData appele', ['data' => $data, 'record' => $record])
        ->once();   
        
        $logSpy->shouldHaveReceived('info')
        ->with('EventBuilder Data handleMentor genere')
        ->once();      

        $logSpy->shouldHaveReceived('info')
        ->with('Donnees generes', \Mockery::on(function ($context) {
            // Vérifie la présence de la clé 'marinUuid' et sa valeur
            return isset($context['marinUuid']) && $context['marinUuid'] === $this->marin->uuid;
        }))
        ->once();
      
    $logSpy->shouldHaveReceived('info')
        ->with('Event cree', \Mockery::type('array'))
        ->once();

        // Dans Model StoredEvent->CreateFromUserGeneratedEvent
        $logSpy->shouldHaveReceived('info')
        ->with('CreateFromUserGeneratedEvent Array cree')
        ->once();
        
        // Dans le Listener MentorMarinListener
        $logSpy->shouldHaveReceived('info')
        ->with('MentorMarinListener appele', \Mockery::on(function ($context) {
            // Vérifie la présence de la clé 'marinUuid' et sa valeur
            return isset($context['marinUuid']) && $context['marinUuid'] === $this->marin->uuid;
        }))
        ->once();

        $logSpy->shouldHaveReceived('info')
        ->with('fcmcentral_fcm_marin  :', \Mockery::on(function ($context) {
            // Vérifie la présence de la clé 'marinUuid' et sa valeur
            return isset($context['uuid']) && $context['uuid'] === $this->marin->uuid;
        }))
        ->once();

});


// Si on envoie une erreur , retour Warning Donnees NON generes 
it('gère le cas où aucun type d\'événement n\'est trouvé', function() {
    // Etant donné
    $record = $this->marin;
    
    $data = [
        'invalid_key' => 'invalid_value',
        'marinUuid' => $this->marin->uuid
    ];

    // Utiliser spy au lieu de mock
    $logSpy = Log::spy();

    // Quand
    EventTriggerService::triggerEvent($data, $record);
    //dd($logSpy);
    // Retour logs
    // $logSpy->shouldHaveReceived('info')->atLeast()->once();
    $logSpy->shouldHaveReceived('warning')
        ->with('Donnees NON generes')
        ->once();
    
    //  Fake les événements
    Event::fake([UserGeneratedEvent::class]);
    
    // Alors - Aucun événement ne doit être déclenché
    Event::assertNotDispatched(UserGeneratedEvent::class);
});
 
// ========================================
// TEST EVENT PAR MULTI DATA
// ========================================

test('teste avec plusieurs types d\'événements (mentor + cohorte)', function() {
    // Etant donné
    $mentor = Marin::factory()->create(['nom' => 'Capitaine Mentor']); 
    $cohorte = Cohorte::factory()->create(['libelle_court' => 'Cohorte Test']);
    $record = $this->marin;
    
    $data = [
        'mentor_id' => $mentor->id,
        'cohorte_id' => $cohorte->id,
        'marinUuid' => $this->marin->uuid
    ];
    
    // Fake les événements
    Event::fake([UserGeneratedEvent::class]);
    
    // Quand
    EventTriggerService::triggerEvent($data, $record,);
    
    // Alors - Vérifier que l'événement contient les deux types
    Event::assertDispatched(UserGeneratedEvent::class, function ($event) use ($cohorte, $mentor){
        //dd($cohorte->uuid,$event->object_uuid[EventTypeManager::ATTRIBUER_COHORTE_MARIN],$mentor->uuid,$event->object_uuid[EventTypeManager::ATTRIBUER_MENTOR_MARIN]);
        // Verifie les evenements
        expect($event->event_type)->toBeArray();
        expect($event->event_type)->toContain(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
        expect($event->event_type)->toContain(EventTypeManager::ATTRIBUER_COHORTE_MARIN);
        // Verification de USER UUID
        expect($event->user_uuid)->toBe($this->user->uuid);
        // Verification de MARIN UUID
        expect($event->marin_uuid)->toBe($this->marin->uuid);
        // Verifie les infos OBJECT CLASS
        expect($event->object_class)->toBeArray();
        expect($event->object_class)->toHaveKey(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
        expect($event->object_class)->toHaveKey(EventTypeManager::ATTRIBUER_COHORTE_MARIN);
        expect($event->object_class[EventTypeManager::ATTRIBUER_COHORTE_MARIN])->toBe('Cohorte');
        expect($event->object_class[EventTypeManager::ATTRIBUER_MENTOR_MARIN])->toBe('FcmMarin');
        // Verifie les infos OBJECT UUID
        expect($event->object_uuid)->toBeArray();
        expect($event->object_uuid)->toHaveKey(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
        expect($event->object_uuid)->toHaveKey(EventTypeManager::ATTRIBUER_COHORTE_MARIN);
        expect($event->object_uuid[EventTypeManager::ATTRIBUER_COHORTE_MARIN])->toBe($cohorte->uuid);
        expect($event->object_uuid[EventTypeManager::ATTRIBUER_MENTOR_MARIN])->toBe($mentor->uuid);
        // Vérifier les détails pour les deux types
        expect($event->detail)->toHaveKey(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
        expect($event->detail)->toHaveKey(EventTypeManager::ATTRIBUER_COHORTE_MARIN);
        expect($event->detail[EventTypeManager::ATTRIBUER_COHORTE_MARIN]['cohorte_libelle_court'])->toBe($cohorte->libelle_court);
        expect($event->detail[EventTypeManager::ATTRIBUER_MENTOR_MARIN]['mentor_nom'])->toBe($mentor->nom);
        
        return true;
    });
});
 
// Verification si ca sauvegarde  dans les 3 listeners de Events (2 operationnels pour l'instant)
test('vérifie l\'intégration complète avec les 3 listeners', function() {
    // Etant donné
    $mentor = Marin::factory()->create(['nom' => 'Capitaine Mentor']); 
    $record = $this->marin;
    
    $data = [
        'mentor_id' => $mentor->id,
        'marinUuid' => $this->marin->uuid
    ];
    
    // Vérifier état initial
    expect(StoredEvent::count())->toBe(0);
    expect(FcmMarin::count())->toBe(0);
    
    // Quand
    EventTriggerService::triggerEvent($data, $record);
    
    // Alors - Vérifier que les 3 listeners ont agi
    
    // 1. StoreUserGeneratedEventListener
    expect(StoredEvent::count())->toBe(1);
    $storedEvent = StoredEvent::first();
    expect($storedEvent->event_type)->toBe(EventTypeManager::ATTRIBUER_MENTOR_MARIN);
    
    
    // 2. SaveUserGeneratedEventListener
    expect(FcmMarin::count())->toBe(1);
    $fcmMarin = FcmMarin::first();
 
    expect($fcmMarin->mentor_id)->toBe($mentor->id);
    expect($fcmMarin->rh_marin_id)->toBe($this->marin->id);
    
    // 3. TransmitUserGeneratedEventListener
    
    
    // Vérifier la cohérence des données
    expect($storedEvent->marin_uuid)->toBe($fcmMarin->marin->uuid);
    expect($storedEvent->detail['mentor_nom'])->toBe('Capitaine Mentor');
});
