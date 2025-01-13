<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Chambre\ChambreController;
use App\Http\Controllers\Chambre\ChambreTypeController;
use App\Http\Controllers\Chambre\ReservationController;
use App\Http\Controllers\Chambre\TarificationController;
use App\Http\Controllers\Hotel\HotelController;
use App\Http\Controllers\TypesChambre\TypesChambreController;
use App\Http\Controllers\User\UserController;
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
    Route::post("storeClientSec",[UserController::class,"storeClient"]);
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

// Room
Route::prefix("/chambre/type")->middleware("auth:sanctum")->group(function (){
    Route::post("store",[ChambreTypeController::class,"createRoomType"]);
    Route::get("show",[ChambreTypeController::class,"showTypes"]);
    Route::get("show/{id}",[ChambreTypeController::class,"show"]);
    Route::get("showAll",[ChambreTypeController::class,"showAll"]);
    Route::delete("delete/{id}",[ChambreTypeController::class,"delete"]);
});
Route::prefix("/chambre")->middleware("auth:sanctum")->group(function (){
    Route::post("store",[ChambreController::class,"storeChambre"]);
    Route::get("showAll",[ChambreController::class,"showAllRoom"]);
    Route::get("showAllRoomWithTarifs",[ChambreController::class,"showAllRoomWithTarifs"]);
    Route::get("maintenance/{id}",[ChambreController::class,"roomMaintenance"]);
    Route::get("available/{id}",[ChambreController::class,"roomAvailable"]);
    Route::get("showRoomWithTarifs/{id}",[ChambreController::class,"showRommWithTarifs"]);
    Route::get("showRoomWithPriceSeason",[ChambreController::class,"showRoomWithPriceSeason"]);
    Route::get("showSRoomWithPriceSeason/{id}",[ChambreController::class,"showSRoomWithPriceSeason"]);
});

Route::prefix("/chambre")->group(function (){
    Route::get("show/{id:id}",[ChambreController::class,"showChambre"]);
    Route::post("searchRoom",[ChambreController::class,"getAvailableRooms"]);
});
//tarification
Route::prefix("/chambre/tarification")->middleware("auth:sanctum")->group(function (){
    Route::post("store",[TarificationController::class,"storeTarif"]);
    Route::get("showAllTarif",[TarificationController::class,"showAllTarif"]);
    Route::get("show/{id} ",[TarificationController::class,"showAllTarifById"]);
    Route::get("showEdit/{id} ",[TarificationController::class,"showTarifById"]);
    Route::delete("delete/{id} ",[TarificationController::class,"delete"]);
});
//reservation
Route::prefix("/reservation")->middleware("auth:sanctum")->group(function (){
    Route::post("/getReserv",[ReservationController::class,"showReservationByHotelUser"]);
    Route::get("/checkin/{id} ",[ReservationController::class,"checkIn"]);
    Route::get("/checkout/{id}",[ReservationController::class,"checkOut"]);
    Route::get("/show/{id}",[ReservationController::class,"showReservationById"]);
    Route::delete("/delete/{id}",[ReservationController::class,"deleteReserv"]);
});

Route::post("/reservation/storeReserv",[ReservationController::class,"storeReservation"]);

//client
Route::prefix("/client")->middleware("auth:sanctum")->group(function (){
    Route::get("",[UserController::class,"getClient"]);
    Route::get("show/{id}",[UserController::class,"getClientByID"]);
});

//employee
Route::prefix("/employee")->middleware("auth:sanctum")->group(function (){
    Route::get("",[UserController::class,"getFDA"]);
    Route::get("show/{id}",[UserController::class,"getFDAByID"]);
});
