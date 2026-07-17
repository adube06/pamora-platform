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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('owner_id')->unique()->constrained('users')->restrictOnDelete();
            $table->string('business_name');
            $table->json('categories');
            $table->json('service_areas')->nullable();
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->string('verification_status')->default('pending');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
