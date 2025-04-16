<?php

namespace Modules\FcmCommun\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

class CreateParcoursSerialisesMigration extends Migration
{
    public $prefix = '';
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        $default_value = "";

        if (DB::connection() instanceof \Illuminate\Database\PostgresConnection) {
            $default_value = DB::raw('(gen_random_uuid())');
        }
        elseif (DB::connection() instanceof \Illuminate\Database\MySqlConnection){
            $default_value = DB::raw('(UUID())');
        }

        Schema::create($this->prefix . 'parcours_serialises', function (Blueprint $table) use ($default_value) {
            //$table->uuid('id')->primary();
            $table->id();
            $table->uuid('uuid')->default($default_value);
            $table->string('libelle_long');
            $table->string('libelle_court');
            $table->integer('version')->default(1);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->json('parcours')->nullable();
            $table->foreignid('parcours_id')->constrained('fcmcentral_parcours');
            
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
