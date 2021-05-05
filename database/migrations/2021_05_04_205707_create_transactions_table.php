<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_from');
            $table->unsignedBigInteger('wallet_to');
            $table->unsignedBigInteger('transaction_status_id');
            $table->unsignedDecimal("amount", 10, 2);
            $table->unsignedDecimal("fee", 8, 2);
            $table->decimal("rate", 10, 6);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('wallet_from')
                ->references('id')
                ->on('wallets')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('wallet_to')
                ->references('id')
                ->on('wallets')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('transaction_status_id')
                ->references('id')
                ->on('transaction_statuses')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
