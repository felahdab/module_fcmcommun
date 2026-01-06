<?php

namespace Modules\FcmCommun\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

class CreateEtatNoteTypeNoteMigration extends Migration
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

        // Creation table FcmCentralTypeNotes
     Schema::create($this->prefix . 'type_notes', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->string('libelle_court');
        $table->timestamps();
    });


       // Creation table FcmCentralNotes
       Schema::create($this->prefix . 'notes', function (Blueprint $table) use ($default_value) {
        $table->id();
        $table->uuid('uuid')->default($default_value);
        $table->string('libelle');
        //$table->foreignId('marin_id')->constrained($this->prefix . 'fcm_marins')->onDelete('cascade');
        $table->foreignId('rh_marin_id')->constrained('rh_marins')->onDelete('cascade');
        //$table->foreignId('marin_id')->constrained('rh_marins')->onDelete('cascade');
        $table->foreignId('type_note_id')->nullable()->constrained($this->prefix . 'type_notes')->onDelete('set null');
        $table->date('date')->nullable();
        //$table->string('redacteur');
       // $table->foreignId('redacteur_id')->nullable()->constrained($this->prefix . 'fcm_marins')->onDelete('set null'); 
       $table->foreignId('redacteur_id')->nullable()->constrained('rh_marins')->onDelete('set null');
       $table->boolean('valide_bcf')->default(false);
        $table->string('lienurl')->nullable();
        $table->string('objet_type')->nullable();
        $table->integer('objet_id')->default(0);
        $table->timestamps();
    });

    
    Schema::create($this->prefix .'type_etats', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique(); // ex: en_fcm, en_sedimentation, fin_sedimentation, en_pause, fin_pause
        $table->string('libelle_court');
        $table->timestamps();
    });
    

  

     // Creation table FcmCentralEtats
     Schema::create($this->prefix . 'etats', function (Blueprint $table) use ($default_value) {
        $table->id();
        $table->uuid('uuid')->default($default_value);
        $table->foreignId('rh_marin_id')->constrained('rh_marins')->onDelete('cascade');
        //$table->foreignId('marin_id')->constrained('rh_marins')->onDelete('cascade');
        $table->foreignId('type_etat_id')->constrained($this->prefix . 'type_etats');
        $table->dateTime('date_debut');
        $table->dateTime('date_fin')->nullable();
        $table->timestamps();

    });
     }
 
     /**
      * Reverse the migrations.
      */
     public function down(): void
     {
        // Supp table
        
        Schema::dropIfExists($this->prefix . 'notes');
        Schema::dropIfExists($this->prefix . 'type_notes');
        Schema::dropIfExists($this->prefix . 'etats');
        Schema::dropIfExists($this->prefix . 'type_etats');
     }
 };
 