<?php

use App\Http\Controllers\Api\DgiiReceptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Módulo de Receptor Electrónico (Webhooks para Suplidores y DGII)
Route::prefix('fe')->group(function () {

    // Recepción de e-CF (Suplidores envían aquí)
    Route::post('/recepcion/api/ecf', [DgiiReceptionController::class, 'receiveEcf'])
        ->name('api.ecf.receive');

    // Aprobación Comercial (Receptores envían aquí su respuesta ACECF)
    Route::post('/aprobacioncomercial/api/ecf', [DgiiReceptionController::class, 'receiveCommercialApproval'])
        ->name('api.ecf.commercial-approval');

});
