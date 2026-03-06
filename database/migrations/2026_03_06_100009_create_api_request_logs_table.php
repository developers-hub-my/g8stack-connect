<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_spec_id')->constrained()->cascadeOnDelete();
            $table->foreignId('api_spec_key_id')->nullable()->constrained()->nullOnDelete();
            $table->string('method', 10);
            $table->string('path');
            $table->string('resource_name')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->unsignedSmallInteger('status_code');
            $table->unsignedInteger('latency_ms')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
    }
};
