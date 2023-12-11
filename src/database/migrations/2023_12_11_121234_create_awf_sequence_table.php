<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('AWF_SEQUENCE', function (Blueprint $table) {
            $table->id('SEQUID')->comment('AWF uniquely developed module sequence identifier');
            $table->string('SEPONR', 10)->comment('Porsche Order Number');
            $table->string('SEPSEQ', 10)->comment('Porsche Sequence Number');
            $table->string('SEARNU', 15)->comment('Article Number');
            $table->string('SEARDE', 50)->comment('Article description');
            $table->enum('SESIDE', ['L', 'R'])->comment('Is the product left or right');
            $table->date('SEEXPI')->comment('Delivery expiration date');
            $table->string('SEPILL', 2)->comment('Which pillar of the car (which CSV)');
            $table->string('PRCODE', 32)->comment('Product code (PRODUCT:PRCODE)');
            $table->string('ORCODE', 32)->comment('Order code (ORDERHEAD:ORCODE)');
        });

        Schema::table('AWF_SEQUENCE', function (Blueprint $table) {
            $table->foreign('PRCODE', 'FK_AWF_SEQUENCE_TO_PRODUCT_PRCODE')
                ->references('PRCODE')
                ->on('PRODUCT')
                ->onUpdate('CASCADE');

            $table->foreign('ORCODE', 'FK_AWF_SEQUENCE_TO_ORDERHEAD_ORCODE')
                ->references('ORCODE')
                ->on('ORDERHEAD')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('AWF_SEQUENCE');
    }
};
