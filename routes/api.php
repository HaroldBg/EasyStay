<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Hotel\HotelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// authentification
Route::prefix("auth")->group(function (){
    Route::post("login",[AuthController::class,"login"])->name('login');
    Route::post("storeAdmin",[AuthController::class,"registerAdmin"]);
    Route::post("storeClient",[AuthController::class,"storeClient"]);
});
Route::prefix("auth")->middleware("auth:sanctum")->group(function (){
    Route::post("logout",[AuthController::class,"logout"]);
    Route::post("storeFDA",[AuthController::class,"storeFrontDeskAgent"]);
});
// hotel

Route::get("/hotel",[HotelController::class,"getHotels"]);
Route::prefix("hotel")->middleware("auth:sanctum")->group(function (){
    Route::post("demande",[HotelController::class,"demandeHotel"]);
    Route::post("reject",[HotelController::class,"reject"]);
    Route::get("demandes",[HotelController::class,"getDemands"]);
    Route::get("show/{id:id}",[HotelController::class,"show"]);
    Route::get("showHotel/{id:id}",[HotelController::class,"showHotel"]);
    Route::delete("demande/{id:id}",[HotelController::class,"delete"]);
    Route::delete("{id:id}",[HotelController::class,"deleteHotel"]);
    Route::get("confirm/{id:id}",[HotelController::class,"confirm"]);
});

