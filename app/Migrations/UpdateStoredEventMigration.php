<?php

namespace Modules\FcmCommun\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



class UpdateStoredEventMigration extends Migration
{
    public $prefix = '';
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        

        Schema::table($this->prefix . 'stored_events', function (Blueprint $table)  {
            
            if(Schema::hasColumn($this->prefix . 'stored_events','marin_id')){
                $table->dropColumn('marin_id'); 
            }
            
            $table->uuid('user_uuid')->after('event_type');

            
            $table->uuid('marin_uuid')->nullable(true)->after('user_uuid');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table($this->prefix . 'stored_events', function (Blueprint $table)  {
            $table->dropColumn('user_uuid');

            if(Schema::hasColumn($this->prefix . 'stored_events','marin_uuid')){
                $table->uuid('marin_id'); 
            }
            $table->dropColumn('marin_uuid');

        });
    }
};
