<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_spec_versions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->foreignId('api_spec_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->json('openapi_spec')->nullable();
            $table->json('configuration')->nullable();
            $table->text('change_summary')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['api_spec_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_spec_versions');
    }
};
