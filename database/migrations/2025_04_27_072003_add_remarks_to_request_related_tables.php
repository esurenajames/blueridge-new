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
            $table->text('remarks')->nullable()->after('status');
        });

        Schema::table('request_quotations', function (Blueprint $table) {
            $table->text('remarks')->nullable()->after('status');
        });

        Schema::table('request_purchase_requests', function (Blueprint $table) {
            $table->text('remarks')->nullable()->after('status');
        });

        Schema::table('request_purchase_orders', function (Blueprint $table) {
            $table->text('remarks')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
        
        Schema::table('request_quotations', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });

        Schema::table('request_purchase_requests', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });

        Schema::table('request_purchase_orders', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
};