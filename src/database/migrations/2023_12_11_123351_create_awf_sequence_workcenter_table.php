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
        Schema::create('AWF_SEQUENCE_WORKCENTER', function (Blueprint $table){
            $table->id('SEWCID')->comment('The unique identifier of the table connecting workcenters and sequences of the uniquely developed module of awf');
            $table->unsignedBigInteger('SEQUID')->nullable()->comment('AWF uniquely developed module sequence identifier (AWF_SEQUENCE:SEQUID)');
            $table->string('WCSHNA', 8)->nullable()->comment('Workcenter short name (WORKCENTER:WCSHNA)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('AWF_SEQUENCE_WORKCENTER');
    }
};
