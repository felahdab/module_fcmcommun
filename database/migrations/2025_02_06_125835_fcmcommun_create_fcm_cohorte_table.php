<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
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
        
        Schema::create('fcmcommun_cohortes', function (Blueprint $table) use ($default_value) {
               $table->id('id');
               $table->uuid('uuid')->default($default_value);
               $table->string('libelle_long');
               $table->string('libelle_court');
               $table->date('date_sortie_cours')->nullable();
               $table->timestamps();
            });

       

        // Liaision avec table FcmUniteMarins et FcmCohortes
        // Schema::table('fcmunite_fcm_marins', function (Blueprint $table){
        //     $table->unsignedBigInteger('cohorte_id')->nullable()->after('data');
        //     $table->foreign('cohorte_id')->references('id')->on('fcmcommun_cohortes')->onDelete('cascade');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcmcommun_cohortes');
    }
};
