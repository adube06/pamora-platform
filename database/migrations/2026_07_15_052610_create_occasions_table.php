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
        Schema::create('occasions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('host_id')->constrained('users')->restrictOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('type');
            $table->text('description')->nullable();
            $table->date('primary_date')->nullable();
            $table->string('timezone')->default('Africa/Dar_es_Salaam');
            $table->string('location')->nullable();
            $table->string('visibility')->default('private');
            $table->string('status')->default('draft');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occasions');
    }
};
