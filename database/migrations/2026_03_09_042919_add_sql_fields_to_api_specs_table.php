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
        Schema::table('api_specs', function (Blueprint $table) {
            $table->text('sql_query')->nullable()->after('openapi_spec');
            $table->string('endpoint_name')->nullable()->after('sql_query');
            $table->json('sql_parameters')->nullable()->after('endpoint_name');
            $table->json('result_columns')->nullable()->after('sql_parameters');
        });
    }

    public function down(): void
    {
        Schema::table('api_specs', function (Blueprint $table) {
            $table->dropColumn(['sql_query', 'endpoint_name', 'sql_parameters', 'result_columns']);
        });
    }
};
