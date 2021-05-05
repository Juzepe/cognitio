<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrencyRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('currency_from');
            $table->unsignedBigInteger('currency_to');
            $table->decimal("rate", 10, 6);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('currency_from')
                ->references('id')
                ->on('currencies')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('currency_to')
                ->references('id')
                ->on('currencies')
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
        Schema::dropIfExists('currency_rates');
    }
}
