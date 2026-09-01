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
        Schema::table('newsletter_subscriptions', function (Blueprint $table) {
            $table->string('organisation')->nullable()->after('consent_given');
            $table->string('role')->nullable()->after('organisation');
            $table->json('topics')->nullable()->after('role');
            $table->string('frequency')->default('As Published')->after('topics');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletter_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['organisation', 'role', 'topics', 'frequency']);
        });
    }
};
