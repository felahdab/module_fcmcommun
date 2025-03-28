<?php

namespace Modules\FcmCommun\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



class CreateMarinMigration extends Migration
{
    public $prefix = '';
    /**
     * Run the migrations.
     */

     public function up(): void
     {
         Schema::create($this->prefix . 'marins', function (Blueprint $table) {
            $table->id('id');
            $table->foreignId('rh_marin_id')->constrained('rh_marins')->onDelete('cascade');
            $table->json('data')->nullable();
            $table->foreignId('mentor_id')->nullable()->constrained('rh_marins')->onDelete('cascade');
            //$table->foreignId('mentor_id')->constrained('rh_marins')->onDelete('cascade');
            $table->timestamps();
         });
     }
 
     /**
      * Reverse the migrations.
      */
     public function down(): void
     {
         Schema::dropIfExists($this->prefix . 'marins');
     }
 };
 