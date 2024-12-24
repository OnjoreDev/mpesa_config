<?php

use App\Http\Controllers\MpesaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

//route that sends the stk push request
Route::post('/stk',[MpesaController::class,"stk"])->name('stk');

