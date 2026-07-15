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
        Schema::create('milestone_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milestone_id')->constrained('milestones')->cascadeOnDelete();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['milestone_id', 'task_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milestone_task');
    }
};
