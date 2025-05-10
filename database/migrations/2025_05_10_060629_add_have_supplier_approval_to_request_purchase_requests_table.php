<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_purchase_requests', function (Blueprint $table) {
            $table->boolean('have_supplier_approval')->default(false)->after('status');
        });
    }
    
    public function down(): void
    {
        Schema::table('request_purchase_requests', function (Blueprint $table) {
            $table->dropColumn('have_supplier_approval');
        });
    }
};
