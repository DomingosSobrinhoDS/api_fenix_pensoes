<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SimuladorController;
use App\Http\Controllers\Api\PortalController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::post("/simulador/email", [SimuladorController::class, 'enviar']);
//Route::get("/ty", [SimuladorController::class, 'newTest']);
Route::post("/portal/email", [PortalController::class, 'enviar']);
Route::post("/portal/get_token", [PortalController::class, 'get_token']);
Route::post("/portal/login", [PortalController::class, 'login']);
Route::post("/portal/first_login", [PortalController::class, 'first_log']);
Route::post("/portal/get_information", [PortalController::class, 'get_information']);
Route::post("/portal/save_token", [PortalController::class, 'new_token_api']);
//Route::get("/teste", [PortalController::class, 'teste']);
