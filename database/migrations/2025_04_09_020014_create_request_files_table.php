<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('path');
            $table->integer('size');
            $table->enum('document_type', [
                'request', 
                'quotation', 
                'purchase_request', 
                'purchase_order'
            ])->default('request');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_files');
    }
};