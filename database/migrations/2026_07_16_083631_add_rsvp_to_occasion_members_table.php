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
        Schema::table('occasion_members', function (Blueprint $table) {
            $table->string('rsvp_status')->nullable()->after('permissions');
            $table->dateTime('rsvp_responded_at')->nullable()->after('rsvp_status');
            $table->unsignedInteger('guest_count')->nullable()->after('rsvp_responded_at');
            $table->text('rsvp_message')->nullable()->after('guest_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('occasion_members', function (Blueprint $table) {
            $table->dropColumn(['rsvp_status', 'rsvp_responded_at', 'guest_count', 'rsvp_message']);
        });
    }
};
