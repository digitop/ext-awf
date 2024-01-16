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

        Route::get('/product-feature/get', ['uses' => 'ProductFeaturesController@create'])
            ->name('product-feature.create');
        Route::get('/product-feature/get/colors', ['uses' => 'ProductFeaturesController@colors'])
            ->name('product-feature.colors');
        Route::get('/product-feature/get/materials', ['uses' => 'ProductFeaturesController@materials'])
            ->name('product-feature.materials');
        Route::post('/product-feature/post', ['uses' => 'ProductFeaturesController@show'])
            ->name('product-feature.show');
        Route::post('/product-feature/check', ['uses' => 'ProductFeaturesController@check'])
            ->name('product-feature.check');
    }
);
