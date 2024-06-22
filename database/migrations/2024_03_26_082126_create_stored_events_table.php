<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stored_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('event_type');
            $table->integer('user_id'); // Doit devenir un uuid ultérieurement
            $table->string('object_class');
            $table->uuid('object_uuid')->nullable(true);
            $table->json('detail')->nullable(true);

            $table->timestamps();

            $table->unique('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stored_events');
    }
};
