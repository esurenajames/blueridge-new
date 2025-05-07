<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_quotations', function (Blueprint $table) {
            $table->enum('have_quotation', ['true', 'false'])->default('false')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('request_quotations', function (Blueprint $table) {
            $table->dropColumn('have_quotation');
        });
    }
};