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
        // Change the enum values for group_name to remove 'MOOE'
        Schema::table('categories', function (Blueprint $table) {
            $table->enum('group_name', [
                'Beginning Cash Balance',
                'Receipts',
                'Expenditures'
            ])->default('Beginning Cash Balance')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add 'MOOE' back to the enum values for group_name
        Schema::table('categories', function (Blueprint $table) {
            $table->enum('group_name', [
                'Beginning Cash Balance',
                'Receipts',
                'Expenditures',
                'MOOE'
            ])->default('Beginning Cash Balance')->change();
        });
    }
};