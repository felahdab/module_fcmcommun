<?php

namespace Modules\FcmCommun\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateNoteLibelleToTextMigration extends Migration
{
    public $prefix = '';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table($this->prefix . 'notes', function (Blueprint $table) {
            $table->text('libelle')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table($this->prefix . 'notes', function (Blueprint $table) {
            $table->string('libelle', 255)->change();
        });
    }
}
