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
        Schema::create('user_activity', function (Blueprint $table) {
            $table->id();
            $table->timestamp('timestamp');
            $table->unsignedBigInteger('user_id');
            $table->string('method', 10);
            $table->unsignedSmallInteger('status_code');
            $table->string('status_name', 30);
            $table->string('route_name', 50);
            $table->string('route', 100);
            $table->ipAddress('ip_address');
            $table->string('user_agent');
            $table->string('description')->nullable();

            $table->index(['timestamp', 'user_id', 'status_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activity');
    }
};
