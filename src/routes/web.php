<?php
use Illuminate\Support\Facades\Route;

Route::group(
    ['middleware' => 'public', 'prefix' => '/web/ext/awf-extension/display', 'namespace' => 'AWF\Extension\Controllers\Web'],
    function () {
        Route::get('preparation-panel', ['uses' => 'PreparationStationPanelController@create'])
            ->name('awf-preparation-panel');
});

Route::group(
    ['middleware' => 'public', 'prefix' => '/web/ext/awf-extension/display', 'namespace' => 'AWF\Extension\Controllers\Web'],
    function () {
        Route::get('shift-management', ['uses' => 'ShiftManagementPanelController@create'])
            ->name('awf-shift-management-panel');
});