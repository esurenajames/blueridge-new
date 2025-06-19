<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE request_timeline MODIFY COLUMN processed_status ENUM('resubmitted', 'processed', 'voided', 'submitted') NULL");
        } elseif (Schema::getConnection()->getDriverName() === 'sqlite') {
            // SQLite does not support ENUM or MODIFY COLUMN.
            // You may need to recreate the table or treat as a TEXT with validation.
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE request_timeline MODIFY COLUMN processed_status ENUM('resubmitted', 'processed', 'voided') NULL");
        } elseif (Schema::getConnection()->getDriverName() === 'sqlite') {
            // Similar comment as above.
        }
    }
};