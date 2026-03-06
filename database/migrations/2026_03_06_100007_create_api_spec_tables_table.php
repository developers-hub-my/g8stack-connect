<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_spec_tables', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->foreignId('api_spec_id')->constrained()->cascadeOnDelete();
            $table->string('table_name');
            $table->string('resource_name');
            $table->json('operations')->nullable();
            $table->json('configuration')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['api_spec_id', 'table_name']);
            $table->unique(['api_spec_id', 'resource_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_spec_tables');
    }
};
