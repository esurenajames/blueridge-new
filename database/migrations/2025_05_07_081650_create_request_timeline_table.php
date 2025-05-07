<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_timeline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->nullable()->constrained('requests')->onDelete('cascade');
            
            // Approver details
            $table->foreignId('approver_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->dateTime('approved_date')->nullable();
            $table->enum('approved_progress', [
                'Request Form',
                'Quotation',
                'Purchase Request',
                'Purchase Order'
            ])->nullable();
            $table->enum('approved_status', [
                'approved',
                'declined',
                'returned'
            ])->nullable();
            
            // Processor details
            $table->foreignId('processor_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->dateTime('processed_date')->nullable();
            $table->enum('processed_progress', [
                'Request Form',
                'Quotation',
                'Purchase Request',
                'Purchase Order'
            ])->nullable();
            $table->enum('processed_status', [
                'resubmitted',
                'processed',
                'voided'
            ])->nullable();

            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_timeline');
    }
};