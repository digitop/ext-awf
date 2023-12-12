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
        Schema::connection('custom_mysql')->create('AWF_SEQUENCE_WORKCENTER', function (Blueprint $table){
            $table->id('SEWCID')->comment('The unique identifier of the table connecting workcenters and sequences of the uniquely developed module of awf');
            $table->unsignedBigInteger('SEQUID')->comment('AWF uniquely developed module sequence identifier (AWF_SEQUENCE:SEQUID)');
            $table->string('WCSHNA', 8)->comment('Workcenter short name (WORKCENTER:WCSHNA)');
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
