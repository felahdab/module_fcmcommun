<?php

namespace Modules\FcmCommun\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



class CreateMarinParcoursMigration extends Migration
{
    public $prefix = '';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->prefix . 'marin_parcours', function (Blueprint $table) {
            
            $table->id('id');
            $table->foreignId('marin_id')->constrained($this->prefix . 'marins')->onDelete('cascade');
            $table->string('taux_global');
            $table->string('taux_stage');
            $table->string('taux_activite');
            // La relation ci-dessous emporte avec elle l'uuid du parcours et la version de celui-ci.
            $table->foreignid('parcoursserarialises_id')->constrained($this->prefix . 'parcours_serialises')->onDelete('cascade');
            $table->json('parcoursmarin')->nullable();
            $table->timestamps();

            // Ancien
            //$table->uuid('id')->primary()->default(DB::raw('UUID()'));
            //$table->foreignUuid('marin_id');
            // La relation ci-dessous emporte avec elle l'uuid du parcours et la version de celui-ci.
            //$table->foreignUuid('parcours_id')->references('id')->on('fcmcentral_parcours_serialises');
            //$table->json('parcours');
            //$table->json('data')->nullable()->default(null);
            //$table->timestamps();
            //$table->unique(['marin_id', 'parcours_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'marin_parcours');
    }
};
