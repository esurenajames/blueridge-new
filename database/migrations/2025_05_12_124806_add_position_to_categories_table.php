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
        Schema::table('categories', function (Blueprint $table) {
            $table->integer('position')->after('group_name');
            // Create a composite unique index on group_name and position
            $table->unique(['group_name', 'position']);
        });

        // Set initial positions for existing categories
        DB::statement('
            UPDATE categories c1
            JOIN (
                SELECT id, group_name,
                    ROW_NUMBER() OVER (PARTITION BY group_name ORDER BY created_at) as position
                FROM categories
            ) c2 ON c1.id = c2.id
            SET c1.position = c2.position
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['group_name', 'position']);
            $table->dropColumn('position');
        });
    }
};