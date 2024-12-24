<?php

use App\Http\Controllers\MpesaResponseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MpesaController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Route to generate token
Route::post('/token',[MpesaController::class,"generateToken"]);

//Route to perform the stk push
Route::post("/stk",[MpesaController::class,"stk_push"]);


//route that registers the urls that will receive safaricom response
Route::post('/register_urls',[MpesaController::class,"registerUrls"]);

//route to simulate the transaction
Route::post("/simulate_c2b",[MpesaController::class,"c2b"]);

//routes to retrieve the mpesa response
//route that retrieves response from safaricom stk push
Route::post('/stk_res',[MpesaResponseController::class,"stkPush"]);

//route that retrieves confirmation response
Route::post('/confirmation',[MpesaResponseController::class,"confirmation"]);

//route that retrieves validation response
Route::post('/validation',[MpesaResponseController::class,"confirmation"]);
