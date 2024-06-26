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
        $database = config('database.connections.mysql.database');

        Schema::connection('custom_mysql')->create('AWF_SEQUENCE_LOG', function (Blueprint $table) {
            $table->id('SELOID')->comment('The unique identifier of the sequence log table of a unique development module of awf');
            $table->unsignedBigInteger('SEQUID')->comment('AWF uniquely developed module sequence identifier (AWF_SEQUENCE:SEQUID)');
            $table->string('WCSHNA', 8)->nullable()->comment('Workcenter short name (WORKCENTER:WCSHNA)');
            $table->timestamp('LSTIME')->nullable()->comment('Timestamp when the workcenter started the work process');
            $table->timestamp('LETIME')->nullable()->comment('Timestamp when the workcenter ended the work process');
            $table->unique(['SEQUID', 'WCSHNA', 'LSTIME', 'LETIME'], 'SEQUID_WCSHNA_LSTIME_LETIME_UNIQUE');
        });

        Schema::connection('custom_mysql')->table('AWF_SEQUENCE_LOG', function (Blueprint $table) use ($database) {
            $table->foreign('SEQUID', 'FK_AWF_SEQUENCE_LOG_TO_AWF_SEQUENCE_SEQUID')
                ->references('SEQUID')
                ->on('AWF_SEQUENCE')
                ->onUpdate('CASCADE');

            $table->foreign('WCSHNA', 'FK_AWF_SEQUENCE_LOG_TO_WORKCENTER_WCSHNA')
                ->references('WCSHNA')
                ->on($database . '.WORKCENTER')
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
        Schema::connection('custom_mysql')->dropIfExists('AWF_SEQUENCE_LOG');
    }
};
