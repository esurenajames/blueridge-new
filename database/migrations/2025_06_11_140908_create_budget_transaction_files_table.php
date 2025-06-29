<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_transaction_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_transaction_history_id')
                ->constrained('budget_transaction_history')
                ->onDelete('cascade');
            $table->string('name');
            $table->string('path');
            $table->integer('size');
            $table->enum('file_type', [
                'image',
                'pdf'
            ])->default('image');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_transaction_files');
    }
};