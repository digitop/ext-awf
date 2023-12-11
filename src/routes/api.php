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
//        Route::post('/{DHIDEN}/check-barcode', ['uses' => 'BarcodeScanController@store'])
//            ->name('liss-barcode-scan.store');
//        Route::delete('/{DHIDEN}/delete-barcode', ['uses' => 'BarcodeScanController@destroy'])
//            ->name('liss-barcode-scan.destroy');
    }
);
