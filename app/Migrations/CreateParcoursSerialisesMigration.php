<?php

namespace Modules\FcmCommun\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParcoursSerialisesMigration extends Migration
{
    public $prefix = '';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->prefix . 'parcours_serialises', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('uuid');
            $table->string('libelle_long');
            $table->string('libelle_court');
            $table->integer('version')->default(1);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->json('parcours');
            
            $table->timestamps();

            $table->unique(['uuid', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'parcours_serialises');
    }
};
