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
        Schema::create('budget_transaction_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->nullable()->constrained('requests')->nullOnDelete();
            $table->foreignId('budget_id')->constrained('budget')->restrictOnDelete();
            $table->foreignId('processed_by')->constrained('users')->restrictOnDelete();
            $table->date('transaction_date');
            $table->enum('type', ['proposed budget', 'expenses', 'profit']);
            $table->decimal('total_amount', 15, 2);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->index(['budget_id', 'type']);
            $table->index('transaction_date');
            $table->index('processed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_transaction_history');
    }
};