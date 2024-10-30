<?php

namespace App\Http\Controllers\Chambre;

use App\Enums\ChambreStatus;
use App\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chambre\StoreChambreRequest;
use App\Models\Chambre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChambreController extends Controller
{

    public function storeChambre(StoreChambreRequest $request) :JsonResponse
    {
        $user = Auth::user();
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");
        $validatorData = $request->validated();
        // let check if this room already exist in this hotel
        $roomExist = Chambre::query()
            ->where('num',$request->num)
            ->where('hotel_id',$user->hotels_id)
            ->exists();
        if ($roomExist){
            return response()->json([
                "error"=>true,
                "message"=>"Chambre déjà existante",
            ]);
        }
        // first of all let create the room
        $room = Chambre::create([
            "num"=>$request->num,
            "description"=>$request->description,
            "types_chambres_id"=>$request->types_chambres_id,
            "hotel_id"=>$user->hotels_id,
            "users_id"=>Auth::id(),
            "statut"=>ChambreStatus::AVAILABLE,
        ]);
        if (!$room){
            return response()->json([
                "error"=>true,
                "message"=>"la création de la chambre a échoué."
            ]);
        }
        // let insert image
        if ($request->hasFile('images')){
            foreach ($request->file('images') as $image) {
                $fileName = "Room_".$request->num."_". time().".".$image->getClientOriginalExtension();
                $filePath = $image->storeAs('images/Chambre',$fileName,'public');
                $room->chambreImage()->create(['image_path' => $filePath]);
            }
        }
        $images = $room->chambreImage;
        return response()->json([
            'error'=>false,
            'message' => 'Chambre et images creer.',
            'room' => $room,
            'images'=>$images,
        ], 200);
    }

    public function showAllRoom()
    {
        $user = Auth::user();
        abort_if(!in_array($user->role, [UserRoles::ADMIN, UserRoles::FRONTDESKAGENT, UserRoles::SUDO]), 403, "Accès Refusé");
        $rooms = Chambre::query()
            ->where('statut',"!=",ChambreStatus::DELETED)
            ->where('hotel_id',$user->hotels_id)
            ->with('chambreImage')
            ->get();
        if (!$rooms){
            return response()->json([
                "error"=>false,
                "message"=>"no room disponible"
            ]);
        }
        return response()->json([
            "error"=>false,
            "message"=>"liste des chambres de votre hotel",
            "chambres"=>$rooms
        ]);
    }

    public function showChambre($id){
        //let find the Room
        $room = Chambre::query()->find($id);
        if (!$room){
            return response()->json([
                "error"=>false,
                "message"=>"chambre inexistante."
            ]);
        }
        $room->chambreImage;
        return response()->json([
            "error"=>false,
            "message"=>"Chambre",
            "chambre"=>$room,
        ]);
    }
}
