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
            $table->foreignId('fcmmarin_id')->constrained($this->prefix . 'fcm_marins')->onDelete('cascade');
            // La relation ci-dessous emporte avec elle l'uuid du parcours et la version de celui-ci.
            $table->foreignid('parcoursserialise_id')->constrained($this->prefix . 'parcours_serialises')->onDelete('cascade');
            $table->decimal('taux_global',5,2)->nullable();
            $table->decimal('taux_stage',5,2)->nullable();
            $table->decimal('taux_activite',5,2)->nullable();
            
            $table->json('parcoursmarin')->nullable();
            $table->timestamps();

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
