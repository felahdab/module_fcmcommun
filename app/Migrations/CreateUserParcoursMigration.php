<?php

namespace Modules\FcmCommun\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

class CreateUserParcoursMigration extends Migration
{
    public $prefix = '';
    public $table_marins='rh_marins';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $table_marins = $this->table_marins;
        Schema::create($this->prefix . 'user_parcours', function (Blueprint $table)  use ($table_marins){
            $table->uuid('id')->primary()->default(DB::raw('UUID()'));
            $table->foreignUuid('user_id')->references('id')->on($table_marins);
            // La relation ci-dessous emporte avec elle l'uuid du parcours et la version de celui-ci.
            $table->foreignUuid('parcours_id')->references('id')->on('fcmcentral_parcours_serialises');

            $table->json('parcours');
            
            $table->timestamps();

            $table->unique(['user_id', 'parcours_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'user_parcours');
    }
};
