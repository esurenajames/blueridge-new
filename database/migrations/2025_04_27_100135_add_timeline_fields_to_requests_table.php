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
            $table->timestamp('approved_at')->nullable()->after('is_completed');
            $table->unsignedBigInteger('approver_id')->nullable()->after('approved_at');
            $table->timestamp('returned_at')->nullable()->after('approver_id');
            $table->unsignedBigInteger('returner_id')->nullable()->after('returned_at');
            $table->timestamp('declined_at')->nullable()->after('returner_id');
            $table->unsignedBigInteger('decliner_id')->nullable()->after('declined_at');

            // Foreign keys (assuming users table)
            $table->foreign('approver_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('returner_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('decliner_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropForeign(['approver_id']);
            $table->dropForeign(['returner_id']);
            $table->dropForeign(['decliner_id']);
            $table->dropColumn([
                'approved_at',
                'approver_id',
                'returned_at',
                'returner_id',
                'declined_at',
                'decliner_id',
            ]);
        });
    }
};