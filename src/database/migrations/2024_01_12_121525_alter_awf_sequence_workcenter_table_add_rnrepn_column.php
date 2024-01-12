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

        Schema::connection('custom_mysql')::table('AWF_SEQUENCE_WORKCENTER', function (Blueprint $table) {
            $table->string('RNREPN', 64)->nullable();
        });

        Schema::connection('custom_mysql')->table('AWF_SEQUENCE_WORKCENTER', function (Blueprint $table) use ($database) {
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

        if (Schema::connection('custom_mysql')::hasColumn('AWF_SEQUENCE_WORKCENTER', 'RNREPN')) {
            Schema::connection('custom_mysql')::table('AWF_SEQUENCE_WORKCENTER', function (Blueprint $table) {
                $table->dropForeign('FK_AWF_SEQUENCE_WORKCENTER_TO_REPNO_RNREPN');
            });
            Schema::connection('custom_mysql')::table('AWF_SEQUENCE_WORKCENTER', function (Blueprint $table) {
                $table->dropColumn('RNREPN');
            });
        }
    }
};
