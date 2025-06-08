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
        Schema::table('requests', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->after('name');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
            $table->dropColumn('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->string('category')->after('name');
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
