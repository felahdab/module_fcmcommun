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

it('a au moins 1 marin en base pour derouler les autres tests', function() {
    $this->assertTrue(Marin::count() > 0);
});

it('peut marquer un marin en suivi en FCM', function() {
    $marin = Marin::first();
    $this->assertTrue($marin->suivi_en_fcm == false);
    $marin->suivi_en_fcm = true;

    $marin->refresh();
    $this->assertTrue($marin->suivi_en_fcm == true);

});

it('peut requeter les marins qui sont en FCM', function() {
    $marin = Marin::first();
    $this->assertTrue($marin->suivi_en_fcm == false);
    $marin->suivi_en_fcm = true;

    $marins = Marin::enFcm()->get();
    $this->assertTrue($marins->contains($marin->id));

});

it('ignore bien les marins qui ne sont pas suivis en FCM quand on requete en filtrant', function() {
    $marin = Marin::first();
    $this->assertTrue($marin->suivi_en_fcm == false);

    $marins = Marin::enFcm()->get();
    $this->assertTrue( ! $marins->contains($marin->id));

});
