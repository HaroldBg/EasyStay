<?php

namespace App\Http\Controllers\Chambre;

use App\Enums\TypeChambreStatus;
use App\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chambre\StoreRoomTypeRequest;
use App\Models\TypesChambre;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChambreTypeController extends Controller
{
    //create room type
    public function createRoomType(StoreRoomTypeRequest $request)
    {
        $user = Auth::user();
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");
        $validatorData = $request->validated();
        // let check if this type is already created
        $typeExist = TypesChambre::query()
            ->where('name',$request->name)
            ->where('hotel_id',$user->hotels_id)
            ->exists();
        if ($typeExist){
            return response()->json([
                "error"=>true,
                "message"=>"Type de chambre existant."
            ],1062);
        }
        // let insert type
        $typeRoom = TypesChambre::create([
            "name"=>$request->name,
            "capacity"=>$request->capacity,
            "features"=>$request->features,
            "hotel_id"=>$user->hotels_id,
            "users_id"=>Auth::id(),
            "status"=>TypeChambreStatus::AVAILABLE->value,
        ]);
        return response()->json([
            "error"=>false,
            "message"=>"Type de chambre créer avec succès",
            "type_chambre"=>$typeRoom
        ],200);
    }
    public function showTypes()
    {
        $user = Auth::user();
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");
        $typeRoom = TypesChambre::query()
            ->where('hotel_id',$user->hotels_id)
            ->where('status',"!=",TypeChambreStatus::DELETED)
            ->get();
        if ($typeRoom->isEmpty()){
            return response()->json([
                "error"=>false,
                "message"=>"Aucun type de chambre créer"
            ],200);
        }
        return response()->json([
            "error"=>false,
            "message"=>"Liste des types de chambre de votre hotel",
            "typeChambre"=>$typeRoom,
        ],200);
    }
    public function showAll()
    {
        $user = Auth::user();
        abort_if($user->role !== UserRoles::SUDO, 403, "Accès Refusé");
        $typeRoom = TypesChambre::all();
        if ($typeRoom->isEmpty()){
            return response()->json([
                "error"=>false,
                "message"=>"Aucun type de chambre créer"
            ],200);
        }
        return response()->json([
            "error"=>false,
            "message"=>"Liste des types de chambre.",
            "typeChambre"=>$typeRoom,
        ],200);
    }

    public function delete($id)
    {
        //let find element
        $typeRoom = TypesChambre::query()->find($id);
        $typeRoom->update([
            'status'=>TypeChambreStatus::DELETED,
        ]);
        return response()->json([
            "error"=>false,
            "message"=>"Type de chambre supprimé avec succès."
        ]);
    }
}
