<?php

namespace App\Http\Controllers\User;

use App\Enums\UserRoles;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreClientRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //
    public function getClient():JsonResponse
    {
        $user = Auth::user();
        abort_if(!in_array($user->role, [UserRoles::ADMIN, UserRoles::FRONTDESKAGENT, UserRoles::SUDO]), 403, "Accès Refusé");
        //$user = Auth::user();
        $hotelId = $user->hotels_id;
        //abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");
        $client = User::query()
                ->where('status', '!=', UserStatus::DELETED)
                ->whereHas('reservations.chambre.hotel', function ($query) use ($hotelId) {
                    $query->where('id', $hotelId);
                })
                ->orWhere('hotels_id',$hotelId)
                ->where('role',UserRoles::CLIENT)
                ->get();

        return response()->json([
            "error"=>false,
            "client"=>$client,
        ],200);
    }

    public function getClientByID($id):JsonResponse
    {
        $user = Auth::user();
        abort_if(!in_array($user->role, [UserRoles::ADMIN, UserRoles::FRONTDESKAGENT, UserRoles::SUDO]), 403, "Accès Refusé");
        //$user = Auth::user();
        $hotelId = $user->hotels_id;
        //abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");
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
            ->where('role', UserRoles::FRONTDESKAGENT)
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
                "message"=>"Réceptionniste introuvable",
            ],200);
        }
        return response()->json([
            "error"=>false,
            "client"=>$client,
        ],200);
    }

    // store client
    public function storeClient(StoreClientRequest $request):JsonResponse{
        $user = Auth::user();
        abort_if(!in_array($user->role, [UserRoles::ADMIN, UserRoles::FRONTDESKAGENT, UserRoles::SUDO]), 403, "Accès Refusé");
        //
        $validatedData = $request->validated();
        $user = User::query()->create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'adresse' => $request->adresse,
            'tel' => $request->tel,
            'picture' => "images/blank_profile.jpeg",
            'password' => Hash::make('00000'),
            'role' => UserRoles::CLIENT->value,
            'status'=>UserStatus::EMAIL_CONFIRMATION_PENDING->value,
            'hotels_id'=>$user->hotels_id,
        ]);
//        Mail::to($user['email'])->send(new AccountValidationMail($user));
        return response()->json([
            "error"=>false,
            "message"=>"Client créé avec succès.",
            'user'=>$user
        ],200);
    }
}
