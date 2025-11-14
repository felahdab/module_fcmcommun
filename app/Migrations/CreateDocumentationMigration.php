<?php

namespace Modules\FcmCommun\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



class CreateDocumentationMigration extends Migration
{
    public $prefix = '';
    /**
     * Run the migrations.
     */

     public function up(): void
     {
         Schema::create($this->prefix . 'documentations', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('titre');
            $table->text('contenu')->nullable();
            $table->integer('ordre')->default(0);
            $table->string('color')->nullable();
            $table->json('droits')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('deftube_url')->nullable();
            $table->timestamps();
         });
     }
 
     /**
      * Reverse the migrations.
      */
     public function down(): void
     {
         Schema::dropIfExists($this->prefix . 'documentations');
     }
 };