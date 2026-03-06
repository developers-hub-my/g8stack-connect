<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_specs', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('name');
            $table->string('api_version')->default('v1')->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('api_specs', function (Blueprint $table) {
            $table->dropColumn(['slug', 'api_version']);
        });
    }
};
