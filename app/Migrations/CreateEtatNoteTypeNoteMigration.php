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

       // Creation table FcmCentralNotes
       Schema::create($this->prefix . 'notes', function (Blueprint $table) use ($default_value) {
        $table->id();
        $table->uuid('uuid')->default($default_value);
        $table->string('libelle');
        $table->foreignId('marin_id')->constrained($this->prefix . 'fcm_marins')->onDelete('cascade');
        $table->date('date')->nullable();
        $table->string('redacteur');
        $table->boolean('valide_bcf')->default(false);
        $table->string('lienurl')->nullable();
        $table->string('objet_type')->nullable();
        $table->integer('obbjet_id')->default(0);
        $table->timestamps();
    });

    // Creation table FcmCentralTypeNotes
     Schema::create($this->prefix . 'typenotes', function (Blueprint $table) {
        $table->id();
        $table->string('libcourt');
        $table->string('liblong');
        $table->timestamps();
    });

    // Liaison avec table FcmCentralNotes et FcmCentralTypeNotes
    Schema::table($this->prefix . 'notes', function (Blueprint $table){
        $table->unsignedBigInteger('typenote_id')->nullable()->after('date');
        $table->foreign('typenote_id')->references('id')->on($this->prefix . 'typenotes')->onDelete('cascade');
    });

     // Creation table FcmCentralEtats
     Schema::create($this->prefix . 'etats', function (Blueprint $table) use ($default_value) {
        $table->id();
        $table->uuid('uuid')->default($default_value);
        $table->foreignId('marin_parcours_id')->constrained($this->prefix . 'marin_parcours')->onDelete('cascade');
        $table->string('raison');
        $table->date('date_debut')->nullable();
        $table->date('date_fin')->nullable();
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
        Schema::dropIfExists($this->prefix . 'typenotes');
        Schema::dropIfExists($this->prefix . 'etats');
     }
 };
 