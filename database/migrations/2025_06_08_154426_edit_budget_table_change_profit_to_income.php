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
            $table->renameColumn('profit', 'income');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget', function (Blueprint $table) {
            $table->renameColumn('income', 'profit');
        });
    }
};