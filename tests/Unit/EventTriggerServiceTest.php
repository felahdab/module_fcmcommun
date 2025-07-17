<?php
 
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\FcmCommun\Services\EventTriggerService;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\StoredEvent;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCentral\Models\Parcours;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Carbon\Carbon;
use Modules\FcmCentral\Database\Seeders\Tests\TestParcoursSeeder;


// Include des helpers
require_once __DIR__ . '/../Helpers/EventTestDataProvider.php';
require_once __DIR__ . '/../Helpers/EventTestLogVerifier.php';
require_once __DIR__ . '/../Helpers/EventTestAssertions.php';
require_once __DIR__ . '/../Helpers/EventTestConfig.php';

 
uses(RefreshDatabase::class);
uses(TestCase::class);
 
pest()->group("EventTriggerService Tests Refactored");
 
// ========================================
// ⚙️ CONFIGURATION INITIALE
// ========================================
 
beforeEach(function () {
    // Créer un utilisateur authentifié
    $this->user = \Modules\RH\Models\User::factory()->create([
        'uuid' => 'user-uuid-123'
    ]);
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
     //'ATTRIBUER_PARCOURS_SERIALISE_MARIN',
     'ERREUR',
]);
 
// ========================================
// ⚙️ TESTS PRINCIPAUX
// ========================================
 
it('🎯 teste EventTrigger avec Logs', function($casTest) {
    // ===== 🏗️ PRÉPARATION =====
    $preparation = EventTestDataProvider::preparerDonneesTest($casTest, $this->marin);
    $setup = $preparation['setup'];
    $data = $preparation['data'];
    $record = $setup['record'];
    
    // ===== 📝 TEST: VÉRIFICATION DES LOGS =====
    $logSpy = Log::spy();
    
    EventTriggerService::triggerEvent($data, $record);
    
    EventTestLogVerifier::verifierLogs(
        $logSpy, 
        $casTest, 
        $data, 
        $record, 
        $this->prefix, 
        $this->marin->uuid
    );
 
})->with('casTestEvenements');
 
it('🎯 teste EventTrigger avec Data builder', function($casTest) {
    // ===== 🏗️ PRÉPARATION =====
    $preparation = EventTestDataProvider::preparerDonneesTest($casTest, $this->marin);
    $setup = $preparation['setup'];
    $data = $preparation['data'];
    $record = $setup['record'];
    
    // ===== 📡 TEST: VÉRIFICATION DE L'ÉVÉNEMENT =====
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
                EventTestAssertions::executerAssertionsEvenement(
                    $evenement, 
                    $setup, 
                    $preparation['assertionsPersonnalisees']['evenement']
                );
            }
            
            return true;
        });
        
        Event::assertDispatchedTimes(UserGeneratedEvent::class, 1);
    }
 
})->with('casTestEvenements');
 
it('🎯 teste EventTrigger avec Base Event', function($casTest) {
    // ===== 🏗️ PRÉPARATION =====
    $preparation = EventTestDataProvider::preparerDonneesTest($casTest, $this->marin);
    $setup = $preparation['setup'];
    $data = $preparation['data'];
    $record = $setup['record'];
 
    // ===== 💾 TEST: VÉRIFICATION DE L'INTÉGRATION =====
    
    // États initiaux
    expect(StoredEvent::count())->toBe(0);
    expect(FcmMarin::count())->toBe(0);
 
    // Exécution réelle
    EventTriggerService::triggerEvent($data, $record);
 
    // Vérifications des événements stockés
    EventTestAssertions::verifierEvenementsStockes($casTest, $preparation);
 
    // Assertions d'intégration personnalisées
    if (isset($preparation['assertionsPersonnalisees']['integration'])) {
        EventTestAssertions::executerAssertionsIntegration(
            $setup, 
            $this->marin, 
            $preparation['assertionsPersonnalisees']['integration']
        );
    }
 
})->with('casTestEvenements');
 
// ========================================
// ⚙️ TESTS SPÉCIFIQUES SIMPLIFIÉS
// ========================================
 
it('🚀 peut attribuer un mentor à un marin', function() {
    $preparation = EventTestDataProvider::preparerDonneesTest('MENTOR', $this->marin);
    $data = $preparation['data'];
    
    Event::fake([UserGeneratedEvent::class]);
    EventTriggerService::triggerEvent($data, $this->marin);
    
    Event::assertDispatched(UserGeneratedEvent::class, function ($event) {
        expect($event->event_type)->toContain('attribuer_mentor_marin');
        return true;
    });
});
 
it('🚀 peut créer un parcours sérialisé', function() {
    $preparation = EventTestDataProvider::preparerDonneesTest('CREATION_PARCOURS_SERIALISE', $this->marin);
    $setup = $preparation['setup'];
    $data = $preparation['data'];
    $record = $setup['record'];
    
    Event::fake([UserGeneratedEvent::class]);
    EventTriggerService::triggerEvent($data, $record);
    
    Event::assertDispatched(UserGeneratedEvent::class, function ($event) {
        expect($event->event_type)->toContain('creation_parcours_serialise');
        return true;
    });
});
 
// it('🚀 peut attribuer un parcours à un marin', function() {
//     $preparation = EventTestDataProvider::preparerDonneesTest('ATTRIBUER_PARCOURS_SERIALISE_MARIN', $this->marin);
//     $setup = $preparation['setup'];
//     $data = $preparation['data'];
//     $record = $setup['record'];
    
//     Event::fake([UserGeneratedEvent::class]);
//     EventTriggerService::triggerEvent($data, $record);
    
//     Event::assertDispatched(UserGeneratedEvent::class, function ($event) {
//         expect($event->event_type)->toContain('attribuer_parcours_serialise_marin');
//         return true;
//     });
// });
 
 
