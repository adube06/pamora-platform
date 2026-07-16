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
        Schema::create('reminder_rules', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('occasion_id')->constrained('occasions')->cascadeOnDelete();
            $table->foreignId('timeline_event_id')->constrained('timeline_events')->cascadeOnDelete();
            $table->unsignedInteger('offset_minutes');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('triggered_at')->nullable();
            $table->timestamps();

            $table->index(['triggered_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_rules');
    }
};
