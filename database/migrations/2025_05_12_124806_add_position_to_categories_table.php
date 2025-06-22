<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->integer('position')->after('group_name')->nullable();
            $table->unique(['group_name', 'position']);
        });

        // Set initial positions for existing categories using PHP for SQLite compatibility
        $categories = DB::table('categories')
            ->orderBy('group_name')
            ->orderBy('created_at')
            ->get();

        $grouped = $categories->groupBy('group_name');
        foreach ($grouped as $groupName => $groupCategories) {
            $position = 1;
            foreach ($groupCategories as $category) {
                DB::table('categories')
                    ->where('id', $category->id)
                    ->update(['position' => $position++]);
            }
        }

        // Make position NOT NULL (after populating values)
        Schema::table('categories', function (Blueprint $table) {
            $table->integer('position')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['group_name', 'position']);
            $table->dropColumn('position');
        });
    }
};