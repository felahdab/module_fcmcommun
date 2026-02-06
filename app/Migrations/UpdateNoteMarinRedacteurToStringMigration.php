<?php

namespace Modules\FcmCommun\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateNoteMarinRedacteurToStringMigration extends Migration
{
    public $prefix = '';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table($this->prefix . 'notes', function (Blueprint $table) {
            // ✅ Supprimer la contrainte de clé étrangère
            $table->dropForeign(['redacteur_id']);
            
            // ✅ Supprimer la colonne redacteur_id
            $table->dropColumn('redacteur_id');
        });

        Schema::table($this->prefix . 'notes', function (Blueprint $table) {
            // ✅ Ajouter la nouvelle colonne redacteur en string
            $table->string('redacteur', 255)->nullable()->after('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table($this->prefix . 'notes', function (Blueprint $table) {
            // ✅ Supprimer la colonne string
            $table->dropColumn('redacteur');
        });

        Schema::table($this->prefix . 'notes', function (Blueprint $table) {
            // ✅ Recréer la clé étrangère
            $table->foreignId('redacteur_id')
                ->nullable()
                ->after('date')
                ->constrained('rh_marins')
                ->onDelete('set null');
        });
    }
}
