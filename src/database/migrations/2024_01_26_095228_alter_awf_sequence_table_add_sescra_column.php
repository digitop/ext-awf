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
        Schema::connection('custom_mysql')->table('AWF_SEQUENCE', function (Blueprint $table) {
            $table->boolean('SESCRA')->default(false)->comment('Remanufacturing due to scrap product');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('custom_mysql')->dropIfExists('AWF_SEQUENCE');

        if (Schema::connection('custom_mysql')->hasColumn('AWF_SEQUENCE', 'SESCRA')) {
            Schema::connection('custom_mysql')->table('AWF_SEQUENCE', function (Blueprint $table) {
                $table->dropColumn('SESCRA');
            });
        }
    }
};
