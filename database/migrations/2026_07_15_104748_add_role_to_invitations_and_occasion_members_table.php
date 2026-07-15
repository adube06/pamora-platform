<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replaces the free-form `responsibilities` (json) + directly-settable
     * `permissions` (json) input with a single required `role`, which
     * resolves permissions via App\Domains\People\Domain\Enums\Role.
     * `occasion_members.permissions` is kept as the derived/cached
     * permission set — it is now always written from Role::permissions(),
     * never accepted as request input.
     */
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn(['responsibilities', 'permissions']);
            $table->string('role')->default('member')->after('email');
            $table->text('notes')->nullable()->after('role');
        });

        Schema::table('occasion_members', function (Blueprint $table) {
            $table->dropColumn('responsibilities');
            $table->string('role')->default('member')->after('status');
            $table->text('notes')->nullable()->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn(['role', 'notes']);
            $table->json('responsibilities')->nullable();
            $table->json('permissions')->nullable();
        });

        Schema::table('occasion_members', function (Blueprint $table) {
            $table->dropColumn(['role', 'notes']);
            $table->json('responsibilities')->nullable();
        });
    }
};
