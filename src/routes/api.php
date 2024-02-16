<?php

use Illuminate\Support\Facades\Route;

/**
 * API global
 * can be called without logging in
 */
Route::group(
    ['middleware' => 'api', 'prefix' => '/api/ext/awf-extension', 'namespace' => 'AWF\Extension\Controllers\Api'],
    function () {
        Route::get('/generate-data', ['uses' => 'GenerateDataController@create'])
            ->name('awf-generate-data.create');
        Route::get('/generate-orders', ['uses' => 'MakeOrderController@create'])
            ->name('awf-generate-orders.create');

        Route::get('/get-default-sequence', ['uses' => 'SequenceController@create'])
            ->name('awf-sequence.create');
        Route::get('/get-sequence/{WCSHNA}/{pillar?}', ['uses' => 'SequenceController@show'])
            ->name('awf-sequence.show');
        Route::post('/move-sequence', ['uses' => 'MoveSequenceController@store'])
            ->name('awf-sequence.store');
        Route::get('/set-sequence/{pillar}/{sequenceId}', ['uses' => 'SequenceController@set'])
            ->name('awf-sequence.set');
        Route::get('/sequence/welder/{pillar}/{WCSHNA}', ['uses' => 'SequenceController@welder'])
            ->name('awf-sequence.set');

        Route::get('/product-feature/get', ['uses' => 'ProductFeaturesController@create'])
            ->name('product-feature.create');
        Route::get('/product-feature/get/colors', ['uses' => 'ProductFeaturesController@colors'])
            ->name('product-feature.colors');
        Route::get('/product-feature/get/materials', ['uses' => 'ProductFeaturesController@materials'])
            ->name('product-feature.materials');
        Route::post('/product-feature/show', ['uses' => 'ProductFeaturesController@show'])
            ->name('product-feature.show');
        Route::post('/product-feature/check', ['uses' => 'ProductFeaturesController@check'])
            ->name('product-feature.check');

        Route::post('/product/check', ['uses' => 'CheckProductController@check'])
            ->name('product.check');

        Route::get('/preparation-panel/default', ['uses' => 'PreparationStationPanelController@default'])
            ->name('awf-preparation-panel.default');

        Route::get('/shift-management/set-default/{dashboardId}', ['uses' => 'ShiftManagementPanelController@default'])
            ->name('shift-management.dashboard.default');

        Route::get('/order/get-code/{dashboardId}', ['uses' => 'OrderController@create'])
            ->name('order.get-code');

        Route::post('/order/check-serial/{dashboardId}', ['uses' => 'OrderController@store'])
            ->name('order.check-serial');
    }
);