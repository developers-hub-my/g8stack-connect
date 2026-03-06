<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_spec_fields', function (Blueprint $table) {
            $table->foreignId('api_spec_table_id')->nullable()->constrained('api_spec_tables')->nullOnDelete()->after('api_spec_id');
            $table->json('validation_rules')->nullable()->after('is_sortable');
        });
    }

    public function down(): void
    {
        Schema::table('api_spec_fields', function (Blueprint $table) {
            $table->dropConstrainedForeignId('api_spec_table_id');
            $table->dropColumn('validation_rules');
        });
    }
};
