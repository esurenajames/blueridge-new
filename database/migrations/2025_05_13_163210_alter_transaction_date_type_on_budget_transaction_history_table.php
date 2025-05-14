<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('budget_transaction_history', function (Blueprint $table) {
            $table->timestamp('transaction_date')->change();
        });
    }

    public function down()
    {
        Schema::table('budget_transaction_history', function (Blueprint $table) {
            $table->date('transaction_date')->change();
        });
    }
};
