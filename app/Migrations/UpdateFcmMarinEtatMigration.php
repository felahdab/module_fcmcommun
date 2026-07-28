<?php

namespace Modules\FcmCommun\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



class UpdateFcmMarinEtatMigration extends Migration
{
    public $prefix = '';
    /**
     * Run the migrations.
     */

     public function up(): void
     {
         Schema::table($this->prefix . 'fcm_marins', function (Blueprint $table) {
           $table->date('date_sedimentation')->nullable()->after('cohorte_id');
           $table->date('date_fcm')->nullable()->after('date_sedimentation');
           $table->date('date_competence')->nullable()->after('date_fcm');
           //$table->boolean('gel')->default(false)->after('date_competence');
            
         });

         
     }
 
     /**
      * Reverse the migrations.
      */
     public function down(): void
     {
        Schema::table($this->prefix . 'fcm_marins', function (Blueprint $table) {
            $table->dropColumn(['date_sedimentation','date_fcm','date_competence']);
        });
     }
 };
 