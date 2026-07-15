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
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('occasion_id')->constrained('occasions')->cascadeOnDelete();
            $table->string('contributor_name');
            $table->string('contributor_phone')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency')->default('TZS');
            $table->string('method');
            $table->text('message')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->date('contributed_at');
            $table->timestamps();

            $table->index(['occasion_id', 'contributed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
