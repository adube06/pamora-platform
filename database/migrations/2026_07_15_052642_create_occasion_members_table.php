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
        Schema::create('occasion_members', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('occasion_id')->constrained('occasions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('invitation_id')->nullable()->constrained('invitations')->nullOnDelete();
            $table->string('status')->default('active');
            $table->json('responsibilities')->nullable();
            $table->json('permissions')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['occasion_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occasion_members');
    }
};
