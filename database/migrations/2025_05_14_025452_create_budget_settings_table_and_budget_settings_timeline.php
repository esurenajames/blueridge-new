<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budget_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); 
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });

        Schema::create('budget_settings_timeline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_setting_id')->constrained('budget_settings')->onDelete('cascade');
            $table->string('action'); 
            $table->unsignedBigInteger('user_id')->nullable(); 
            $table->timestamps();
        });

        DB::table('budget_settings')->insert([
            ['name' => 'budget', 'is_locked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'categories', 'is_locked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'sub_categories', 'is_locked' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_settings_timeline');
        Schema::dropIfExists('budget_settings');
    }
};