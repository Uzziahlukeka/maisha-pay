<?php

use Illuminate\Support\Facades\Route;
use Uzhlaravel\Maishapay\Http\Controllers\MaishapayCallbackController;

Route::group(['prefix' => 'maishapay', 'as' => 'maishapay.'], function () {
    Route::post('callback', [MaishapayCallbackController::class, 'handleCallback'])
        ->name('callback');
});
