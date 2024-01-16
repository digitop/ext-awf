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
        Route::get('/get-sequence/{WCSHNA}', ['uses' => 'SequenceController@show'])
            ->name('awf-sequence.show');
        Route::post('/move-sequence', ['uses' => 'MoveSequenceController@store'])
            ->name('awf-sequence.store');

        Route::get('/get-product-feature', ['uses' => 'ProductFeaturesController@create'])
            ->name('get-product-feature.create');
        Route::get('/get-product-feature/colors', ['uses' => 'ProductFeaturesController@colors'])
            ->name('get-product-feature.colors');
        Route::get('/get-product-feature/materials', ['uses' => 'ProductFeaturesController@materials'])
            ->name('get-product-feature.materials');
        Route::post('/get-product-feature', ['uses' => 'ProductFeaturesController@show'])
            ->name('get-product-feature.show');
    }
);
