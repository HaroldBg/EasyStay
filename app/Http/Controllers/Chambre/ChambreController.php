<?php

namespace App\Http\Controllers\Chambre;

use App\Enums\ChambreStatus;
use App\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chambre\AvailableRoomRequest;
use App\Http\Requests\Chambre\StoreChambreRequest;
use App\Models\Chambre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
    // show all room with room's type and tarification
    public function showAllRoomWithTarifs()
    {
        $user = Auth::user();
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");
        // let get all room with all data
        $rooms = Chambre::query()
            ->where('statut',"!=",ChambreStatus::DELETED)
            ->with('typesChambre.tarifications')->get();
        return response()->json([
            "error"=>false,
            "chambres"=>$rooms
        ],200);
    }
    public function showRommWithTarifs($id)
    {
        $user = Auth::user();
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");
        $rooms = Chambre::query()
            ->where('statut',"!=",ChambreStatus::DELETED)
            ->with('typesChambre.tarifications')->findOrFail($id);
        return response()->json([
            "error"=>false,
            "chambres"=>$rooms
        ],200);
    }
    public function showRoomWithPriceSeason()
    {
        $user = Auth::user();
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");
        // Obtenir le jour et le mois actuels au format "DD-MM"
        $currentDayMonth = Carbon::now()->format('d-m');

        $rooms = Chambre::query()
            ->where('statut',"!=",ChambreStatus::DELETED)
            ->with(['typesChambre.tarifications' => function ($query) use ($currentDayMonth) {
                // Appliquer une condition pour vérifier si le jour et mois actuels sont dans la période
                $query->whereRaw("DATE_FORMAT(date_deb, '%d-%m') <= ?", [$currentDayMonth])
                    ->whereRaw("DATE_FORMAT(date_fin, '%d-%m') >= ?", [$currentDayMonth]);
            }])->get();
        return response()->json([
            "error"=>false,
            "chambres"=>$rooms
        ],200);
    }
    public function showSRoomWithPriceSeason($id)
    {
        $user = Auth::user();
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");
        // Obtenir le jour et le mois actuels au format "DD-MM"
        $currentDayMonth = Carbon::now()->format('d-m');

        $rooms = Chambre::query()
            ->where('statut',"!=",ChambreStatus::DELETED)
            ->with(['typesChambre.tarifications' => function ($query) use ($currentDayMonth) {
                // Appliquer une condition pour vérifier si le jour et mois actuels sont dans la période
                $query->whereRaw("DATE_FORMAT(date_deb, '%d-%m') <= ?", [$currentDayMonth])
                    ->whereRaw("DATE_FORMAT(date_fin, '%d-%m') >= ?", [$currentDayMonth]);
            }])->findOrFail($id);
        return response()->json([
            "error"=>false,
            "chambres"=>$rooms
        ],200);
    }
    //research available room
    public function getAvailableRooms(AvailableRoomRequest $request)
    {

        $validatorData = $request->validated();
        $dateDeb = Carbon::parse($request->date_deb);
        $dateFin = Carbon::parse($request->date_fin);
        $dayMonthDeb = $dateDeb->format('d-m');
        $dayMonthFin = $dateFin->format('d-m');
        $nmb_per = $request->nmb_per;

        // Fetch rooms that are not reserved in the specified date range
        $availableRooms = Chambre::query() 
            ->whereHas('typesChambre', function ($query) use ($nmb_per) {
                $query->where('capacity', '>=', $nmb_per);
            })
            ->whereDoesntHave('reservations', function ($query) use ($dateDeb, $dateFin) {
                $query->where(function ($q) use ($dateDeb, $dateFin) {
                    $q->whereBetween('date_deb', [$dateDeb, $dateFin])
                        ->orWhereBetween('date_fin', [$dateDeb, $dateFin])
                        ->orWhere(function ($query) use ($dateDeb, $dateFin) {
                            $query->where('date_deb', '<=', $dateDeb)
                                ->where('date_fin', '>=', $dateFin);
                        });
                });
            })
            ->with(['typesChambre.tarifications' => function ($query) use ($dayMonthDeb, $dayMonthFin) {
                $query->whereRaw("DATE_FORMAT(date_deb, '%d-%m') <= ?", [$dayMonthDeb])
                    ->whereRaw("DATE_FORMAT(date_fin, '%d-%m') >= ?", [$dayMonthFin]);
            }, 'hotel'])
            ->get();

        // Calculate the total tariff for each room based on the number of nights
        $availableRooms->each(function ($room) use ($dateDeb, $dateFin) {
            $numberOfNights = $dateFin->diffInDays($dateDeb);
            if ($room->typesChambre && $room->typesChambre->tarifications->isNotEmpty()) {
                $tarifApplicable = $room->typesChambre->tarifications->first()->prix * $numberOfNights;
                $room->tarif_applicable = $tarifApplicable;
            } else {
                $room->tarif_applicable = null; // Set null if no applicable tariff found
            }
        });

        return response()->json([
            "error" => false,
            "available_rooms" => $availableRooms,
        ], 200);
    }

}
