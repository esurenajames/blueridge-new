<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestTimelineTable extends Migration
{
    public function up()
    {
        Schema::create('request_timeline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->onDelete('cascade');
            $table->foreignId('approver_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('approved_date');
            $table->enum('approved_progress', [
                'Request Form',
                'Quotation',
                'Purchase Request',
                'Purchase Order'
            ]);
            $table->enum('approved_status', [
                'approved',
                'declined',
                'returned'
            ]);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('request_timeline');
    }
}