<?php

namespace App\Http\Controllers\Chambre;

use App\Enums\TarificationStatus;
use App\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chambre\StoreTarifRequest;
use App\Http\Resources\TarificationResource;
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
        if (!$exist){
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
                "error"=> false,
                "message" => "Tarification enregistrée avec succès",
                "tarification" => $tarif,
            ], 200);

        }
        return response()->json([
            "error"=>true,
            "message"=>"Tarification existante.",
        ],409);

    }

    // showAll tarification
    public function showAllTarif()
    {
        // Get the authenticated user
        $user = Auth::user();
        // Ensure the user is an admin
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");

        // Fetch tarifications for the user's hotel
        // Assuming the User model has a relationship with Hotel and the Tarification model is linked by hotel_id
        $hotelId = $user->hotels_id;
        $tarifs = Tarification::query()
            ->where('status',"!=",TarificationStatus::DELETED)
            ->orderBy('created_at', 'desc')
            ->whereHas('typeChambre', function ($query) use ($hotelId) {
                $query->where('hotel_id', $hotelId);
            })
            ->with('typeChambre')
            ->get();

        // Check if any tarif exists
        if ($tarifs->isEmpty()) {
            return response()->json([
                "error" => false,
                "message" => "Aucune tarification disponible"
            ]);
        }

        // Return the response with the tarifications
        //return  TarificationResource::collection($tarifs);
        return response()->json([
            "error" => false,
            "message" => "Liste des tarifications de votre hôtel",
            "tarifications" => TarificationResource::collection($tarifs)
        ]);
    }
    // show tarif by id
    public function showAllTarifById($id)
    {
        // Get the authenticated user
        $user = Auth::user();
        // Ensure the user is an admin
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");


        $tarifs = Tarification::query()
            ->where('status',"!=",TarificationStatus::DELETED)
            ->where('id',$id)
            ->with('typeChambre')
            ->first();

        // Check if any tarif exists
        if (!$tarifs) {
            return response()->json([
                "error" => true,
                "message" => "Tarification inexistante"
            ]);
        }

        // Return the response with the tarifications
        //return  TarificationResource::collection($tarifs);
        return response()->json([
            "error" => false,
            "message" => "Tarification",
            "tarifications" => new TarificationResource($tarifs)
        ]);
    }

    public function showTarifById($id)
    {
        // Get the authenticated user
        $user = Auth::user();
        // Ensure the user is an admin
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");


        $tarifs = Tarification::query()
            ->where('status',"!=",TarificationStatus::DELETED)
            ->where('id',$id)
            ->with('typeChambre')
            ->first();

        // Check if any tarif exists
        if (!$tarifs) {
            return response()->json([
                "error" => true,
                "message" => "Tarification inexistante"
            ]);
        }

        // Return the response with the tarifications
        //return  TarificationResource::collection($tarifs);
        return response()->json(
             new TarificationResource($tarifs)
        );
    }
    public function delete($id)
    {
        //dd($id);
        //let find element
        $typeRoom = Tarification::query()->find($id);

        // Vérifier si l'élément existe
        if (!$typeRoom) {
            return response()->json([
                "error" => true,
                "message" => "Tarification introuvable. ",
            ], 404);
        }
        $typeRoom->update([
            'status'=>TarificationStatus::DELETED,
        ]);
        return response()->json([
            "error"=>false,
            "message"=>"Tarification supprimé avec succès."
        ]);
    }
}
