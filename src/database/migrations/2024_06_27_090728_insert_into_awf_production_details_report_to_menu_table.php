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
        \App\Models\RIGHTS::insert(array(
            [
                'RIIDEN' => 'REPORT_AWF_PRODUCTION_DETAILS',
                'RIDESC' => 'AWF custom production details',
                'ORIDEN' => 200,
                'RIMODU' => 'report',
                'RIPATH' => 'report/awfProductionDetails'
            ],
        ));

        \App\Models\MENU::insert(array(
            [
                'parent_id' => null,
                'translation_id' => 'ext.awf.porsche.report.productionDetails',
                'RIIDEN' => 'REPORT_AWF_PRODUCTION_DETAILS',
                'order' => 200,
                'url' => 'report/awfProductionDetails',
                'is_folder' => 0,
                'module' => 'report'
            ],
        ));

        \App\Models\RIUSUG::insert(array(
            [
                'RIIDEN' => 'REPORT_AWF_PRODUCTION_DETAILS',
                'UGIDEN' => null,
                'USIDEN' => 1,
                'RLEVEL' => 2
            ],
        ));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
};
