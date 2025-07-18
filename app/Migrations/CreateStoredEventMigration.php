<?php

namespace Modules\FcmCommun\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

class CreateStoredEventMigration extends Migration
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

        Schema::create($this->prefix . 'stored_events', function (Blueprint $table) use ($default_value) {
            $table->id();
            $table->uuid('event_uuid')->default($default_value);
            $table->timestamp('event_datetime')->useCurrent();
            $table->string('event_type');
            $table->uuid('user_uuid');
            $table->uuid('marin_uuid')->nullable(true); // Doit devenir un uuid ultérieurement
            $table->string('object_class');
            $table->uuid('object_uuid')->nullable(true);
            $table->json('detail')->nullable(true);

            $table->timestamps();

            //$table->unique('event_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'stored_events');
    }
};
