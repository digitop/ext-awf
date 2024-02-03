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

        Schema::connection('custom_mysql')->create('AWF_SEQUENCE_WORKCENTER', function (Blueprint $table){
            $table->id('SEWCID')->comment('The unique identifier of the table connecting workcenters and sequences of the uniquely developed module of awf');
            $table->unsignedBigInteger('SEQUID')->comment('AWF uniquely developed module sequence identifier (AWF_SEQUENCE:SEQUID)');
            $table->string('WCSHNA', 8)->comment('Workcenter short name (WORKCENTER:WCSHNA)');
            $table->string('RNREPN', 64)->comment('Reporting number (REPNO:RNREPN)');
        });

        Schema::connection('custom_mysql')->table('AWF_SEQUENCE_WORKCENTER', function (Blueprint $table) use ($database) {
            $table->foreign('SEQUID', 'FK_AWF_SEQUENCE_WORKCENTER_TO_AWF_SEQUENCE_SEQUID')
                ->references('SEQUID')
                ->on('AWF_SEQUENCE')
                ->onUpdate('CASCADE');

            $table->foreign('WCSHNA', 'FK_AWF_SEQUENCE_WORKCENTER_TO_WORKCENTER_WCSHNA')
                ->references('WCSHNA')
                ->on($database . '.WORKCENTER')
                ->onUpdate('CASCADE');

            $table->foreign('RNREPN', 'FK_AWF_SEQUENCE_WORKCENTER_TO_REPNO_RNREPN')
                ->references('RNREPN')
                ->on($database . '.REPNO')
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
        Schema::connection('custom_mysql')->dropIfExists('AWF_SEQUENCE_WORKCENTER');
    }
};
