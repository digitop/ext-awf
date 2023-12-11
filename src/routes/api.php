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
        Route::get('/get-default-sequence', ['uses' => 'SequenceController@create'])
            ->name('awf-sequence.create');
    }
);
