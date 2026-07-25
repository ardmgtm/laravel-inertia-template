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
        Schema::table('user_activity', function (Blueprint $table) {
            $table->json('request_payload')->nullable()->after('description');
            $table->json('response')->nullable()->after('request_payload');
            $table->unsignedInteger('duration_ms')->nullable()->after('response');
            $table->text('error_message')->nullable()->after('duration_ms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_activity', function (Blueprint $table) {
            $table->dropColumn(['request_payload', 'response', 'duration_ms', 'error_message']);
        });
    }
};
