<?php

namespace App\Http\Controllers\User;

use App\Enums\UserRoles;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    //
    public function getClient():JsonResponse
    {
        $user = Auth::user();
        $hotelId = $user->hotels_id;
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");
        $client = User::query()
                ->where('status', '!=', UserStatus::DELETED)
                ->whereHas('reservations.chambre.hotel', function ($query) use ($hotelId) {
                    $query->where('id', $hotelId);
                })
                ->get();

        return response()->json([
            "error"=>false,
            "client"=>$client,
        ],200);
    }

    public function getClientByID($id):JsonResponse
    {
        $user = Auth::user();
        $hotelId = $user->hotels_id;
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");
        $client = User::query()
            ->find($id);
        if (!$client){
            return response()->json([
                "error"=>true,
                "message"=>"Client introuvable",
            ],200);
        }
        return response()->json([
            "error"=>false,
            "client"=>$client,
        ],200);
    }

    public function getFDA():JsonResponse
    {
        $user = Auth::user();
        $hotelId = $user->hotels_id;
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");
        $client = User::query()
            ->where('role', '!=', UserRoles::FRONTDESKAGENT)
            ->where('hotels_id', $hotelId)
            ->get();

        return response()->json([
            "error"=>false,
            "fda"=>$client,
        ],200);
    }

    public function getFDAByID($id):JsonResponse
    {
        $user = Auth::user();
        $hotelId = $user->hotels_id;
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");
        $client = User::query()
            ->find($id);
        if (!$client){
            return response()->json([
                "error"=>true,
                "message"=>"Client introuvable",
            ],200);
        }
        return response()->json([
            "error"=>false,
            "client"=>$client,
        ],200);
    }
}
