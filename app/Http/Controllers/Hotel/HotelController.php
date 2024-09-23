<?php

namespace App\Http\Controllers\Hotel;

use App\Enums\DemandeSatus;
use App\Enums\HotelStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hotel\DemandeHotel;
use App\Http\Requests\Hotel\RejectHotel;
use App\Models\Demande;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HotelController extends Controller
{
    function demandeHotel(DemandeHotel $request) : JsonResponse
    {
        $validatorData = $request->validated();
//        dd(Auth::id());
        $userID= Auth::id();
//        dd(DemandeSatus::PENDING->value);
        $demande = Demande::query()->create([
            "nom"=>$request->nom,
            "email"=>$request->email,
            "adresse"=>$request->adresse,
            "status"=>DemandeSatus::PENDING->value,
            "users_id"=>$userID,
        ]);

        return response()->json([
            "error"=>false,
            "message"=>"votre demande a été envoyé et est actuellement en attente. Je vous prie de bien vouloir patienter",
            "Demande"=>$demande,
            "Demande_status"=>$demande->status,

        ],200);
    }

    public function getHotels()
    {
        // Get all Demands
        $hotel = Hotel::all();
        return response()->json([
            "error"=>false,
            "Demandes"=>$hotel,

        ],200);
    }
    public function getDemands()
    {
        // Get all Demands
        $demands = Demande::all();
        return response()->json([
            "error"=>false,
            "Demandes"=>$demands,

        ],200);
    }
    public function show($id)
    {
        $demand = Demande::find($id);
        if (!$demand){
            return response()->json([
                "error"=>true,
                "message"=>"Demande non existente"
            ],404);
        }
        return response()->json([
            'error'=>false,
            "Demande"=>$demand,
        ],200);
    }
    public function confirm($id){
        // let find demande
        $demand = Demande::query()->find($id);
        if (!$demand){
            return response()->json([
                "error"=>true,
                "message"=>"Demande non existente"
            ],404);
        }

        $updateDemand = $demand->update([
            "status"=>DemandeSatus::VALIDATE,
        ]);
        // after demand is confirmed we can create the hotel
        if (!$updateDemand){
            $demand = Demande::query()->find($id);
            return response()->json([
                'error'=>true,
                'message'=>'Une erreur est survenue lors de la confirmation de la demande.',
                "Demande"=>$demand,
            ],200);

        }
        $hotel = Hotel::query()->create([
            "nom"=>$demand->nom,
            "email"=>$demand->email,
            "adresse"=>$demand->adresse,
            "status"=>HotelStatus::PENDING->value,
            "users_id"=>$demand->users_id,
        ]);
        if (!$hotel){
            return response()->json([
                'error'=>true,
                'message'=>'Une erreur est survenue lors de la création de '.$demand->nom,
            ],401);
        }

        // let attach this hotel to the admin
        $user = User::find($demand->users_id);
        $user->update(
            [
                "hotels_id"=>$hotel->id,
            ]
        );
        return response()->json([
            'error'=>false,
            'message'=>$demand->nom.' créer avec succès.',
            "Hotel"=>$hotel,
            "user"=>$user,
        ],200);


    }
    public function reject(RejectHotel $request){
        $validatorData = $request->validated();
        $demand = Demande::query()->find($request->id);
        if (!$demand){
            return response()->json([
                "error"=>true,
                "message"=>"Demande non existente",
            ],404);
        }
        $updateDemand = $demand->update([
            "status"=>DemandeSatus::REJECTED->value,
            "motif"=>$request->motif,
        ]);

        $demand = Demande::query()->find($request->id);
        return response()->json([
            "error"=>false,
            "message"=>"Demande rejetée avec succès",
            "demande"=>$demand,
        ],200);
    }
    public function delete($id)
    {
        $demand = Demande::query()->find($id);
        if (!$demand){
            return response()->json([
                "error"=>true,
                "message"=>"Demande non existente",
            ],404);
        }
        $demand->delete();
        return response()->json([
            "error"=>true,
            'message' => 'Demande supprimée avec succès.',
        ], 200);
    }
}
