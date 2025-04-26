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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->text('description')->nullable();
            $table->enum('status', ['draft','pending','approved','declined','voided', 'returned'])->default('draft');
            $table->boolean('is_completed')->default(false);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
    
            // Foreign key if 'created_by' references a users table
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};