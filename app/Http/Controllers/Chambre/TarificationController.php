<?php

namespace App\Http\Controllers\Chambre;

use App\Enums\TarificationStatus;
use App\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chambre\StoreTarifRequest;
use App\Models\Tarification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TarificationController extends Controller
{
    //
    public function storeTarif(StoreTarifRequest $request)
    {
        $user = Auth::user();
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");
        $validatorData = $request->validated();
        //let check if this room's type has already exist
        $exist = Tarification::query()
            ->where('saison',$request->saison)
            ->where('types_chambres_id',$request->types_chambres_id)
            ->exists();
        if ($exist){
            return response()->json([
                "error"=>true,
                "message"=>"la tarification est existante.",
            ]);
        }
        // let insert tha tarif
        $tarif = Tarification::create([
            "prix"=>$request->prix,
            "saison"=>$request->saison,
            "date_deb"=>$request->date_deb,
            "date_fin"=>$request->date_fin,
            "types_chambres_id"=>$request->types_chambres_id,
            "users_id"=>Auth::id(),
        ]);
        return response()->json([
            'message' => 'Tarification enregistrée avec succès',
            'tarification' => $tarif,
        ], 200);
    }

    // showAll tarification
    public function showAllTarif()
    {
        $user = Auth::user();
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");
        //please remember to add status in table
        $tarifs = Tarification::all();
        if (!$tarifs){
            return response()->json([
                "error"=>false,
                "message"=>"Aucun tarif disponible"
            ]);
        }
        return response()->json([
            "error"=>false,
            "message"=>"liste des chambres de votre hotel",
            "chambres"=>$tarifs
        ]);
    }
}
