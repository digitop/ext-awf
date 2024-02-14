<?php
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'middleware' => 'public',
        'prefix' => '/web/ext/awf-extension/display',
        'namespace' => 'AWF\Extension\Controllers\Web'
    ],
    function () {
        Route::get('preparation-panel', ['uses' => 'PreparationStationPanelController@create'])
            ->name('awf-preparation-panel');
        Route::get('welder/{WCSHNA}', ['uses' => 'WelderPanelController@create'])
            ->name('awf-welder-panel');
});

Route::group(
    [
        'middleware' => 'public',
        'prefix' => '/web/ext/awf-extension/display',
        'namespace' => 'AWF\Extension\Controllers\Web\ShiftManagement'
    ],
    function () {
        Route::get('shift-management', ['uses' => 'ShiftManagementPanelController@create'])
            ->name('awf-shift-management-panel.default');
        Route::get('shift-management/shift-start', ['uses' => 'ShiftManagementShiftStartPanelController@create'])
            ->name('awf-shift-management-panel.shift-start');
        Route::get(
            'shift-management/shift-start/{pillar}',
            ['uses' => 'ShiftManagementShiftStartPanelController@index']
        )
            ->name('awf-shift-management-panel.shift-start.index');
        Route::get('shift-management/shift-start/default', ['uses' => 'ShiftManagementShiftStartPanelController@default'])
            ->name('awf-shift-management-panel.shift-start.default');
        Route::get('shift-management/production', ['uses' => 'ShiftManagementProductionPanelController@create'])
            ->name('awf-shift-management-panel.production');
        Route::get('shift-management/production/{WCSHNA}', ['uses' => 'ShiftManagementProductionPanelController@show'])
            ->name('awf-shift-management-panel.production.show');
        Route::get('shift-management/production/{WCSHNA}/data', ['uses' => 'ShiftManagementProductionPanelController@data'])
            ->name('awf-shift-management-panel.production.data');
        Route::get('shift-management/reason', ['uses' => 'ShiftManagementReasonPanelController@create'])
            ->name('awf-shift-management-panel.reason');
        Route::get(
            'shift-management/manual-data-record',
            ['uses' => 'ShiftManagementPanelManualDataRecordController@create']
        )
            ->name('awf-shift-management-panel.manual-data-record');
        Route::get(
            'shift-management/manual-data-record/{WCSHNA}',
            ['uses' => 'ShiftManagementPanelManualDataRecordController@show']
        )
            ->name('awf-shift-management-panel.manual-data-record.show');
        Route::post(
            'shift-management/manual-data-record/{WCSHNA}/update',
            ['uses' => 'ShiftManagementPanelManualDataRecordController@update']
        )
            ->name('awf-shift-management-panel.manual-data-record.update');
});
