<?php
use Illuminate\Support\Facades\Route;

Route::group(
    ['middleware' => 'public', 'prefix' => '/web/ext/awf-extension/display', 'namespace' => 'AWF\Extension\Controllers\Web'],
    function () {
        Route::get('preparation-panel', ['uses' => 'PreparationStationPanelController@create'])
            ->name('awf-preparation-panel');
});

Route::group(
    ['middleware' => 'public', 'prefix' => '/web/ext/awf-extension/display', 'namespace' => 'AWF\Extension\Controllers\Web\ShiftManagement'],
    function () {
        Route::get('shift-management', ['uses' => 'ShiftManagementPanelController@create'])
            ->name('awf-shift-management-panel.default');
        Route::get('shift-management/shift-start', ['uses' => 'ShiftManagementShiftStartPanelController@create'])
            ->name('awf-shift-management-panel.shift-start');
        Route::get('shift-management/shift-start/{pillar}', ['uses' => 'ShiftManagementShiftStartPanelController@index'])
            ->name('awf-shift-management-panel.shift-start.index');
        Route::get('shift-management/production', ['uses' => 'ShiftManagementProductionPanelController@create'])
            ->name('awf-shift-management-panel.production');
        Route::get('shift-management/reason', ['uses' => 'ShiftManagementReasonPanelController@create'])
            ->name('awf-shift-management-panel.reason');
});