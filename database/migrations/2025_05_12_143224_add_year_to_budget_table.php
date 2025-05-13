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
        Schema::table('budget', function (Blueprint $table) {
            $table->year('year')->after('subcategory_id')->default(date('Y'));
            // Create a composite unique index for subcategory_id and year
            $table->unique(['subcategory_id', 'year'], 'budget_subcategory_year_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget', function (Blueprint $table) {
            $table->dropUnique('budget_subcategory_year_unique');
            $table->dropColumn('year');
        });
    }
};