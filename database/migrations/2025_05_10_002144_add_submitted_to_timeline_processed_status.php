<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE request_timeline MODIFY COLUMN processed_status ENUM('resubmitted', 'processed', 'voided', 'submitted') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE request_timeline MODIFY COLUMN processed_status ENUM('resubmitted', 'processed', 'voided') NULL");
    }
};