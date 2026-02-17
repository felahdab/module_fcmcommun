<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Modules\FcmCommun\Models\Marin;

uses(RefreshDatabase::class);
uses(TestCase::class);

pest()->group("Fcm Commun");

beforeEach(function () {
    Marin::factory()->create();
});

it('has at least 1 Marin to work on', function() {
    $this->assertTrue(Marin::count() > 0);
});

it('can mark a Marin as suivi en FCM', function() {
    $marin = Marin::first();
    $this->assertTrue($marin->suivi_en_fcm == false);
    $marin->suivi_en_fcm = true;

    $marin->refresh();
    $this->assertTrue($marin->suivi_en_fcm == true);

});

it('can query Marin which are suivi en FCM', function() {
    $marin = Marin::first();
    $this->assertTrue($marin->suivi_en_fcm == false);
    $marin->suivi_en_fcm = true;

    $marins = Marin::enFcm()->get();
    $this->assertTrue($marins->contains($marin->id));

});

it('can will not show Marin which are not suivi en FCM', function() {
    $marin = Marin::first();
    $this->assertTrue($marin->suivi_en_fcm == false);

    $marins = Marin::enFcm()->get();
    $this->assertTrue( ! $marins->contains($marin->id));

});
