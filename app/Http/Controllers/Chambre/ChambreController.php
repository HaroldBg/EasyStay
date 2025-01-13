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
//Store room
    /**
     * @OA\Post(
     *     path="/api/chambre/store",
     *     summary="Créer une chambre",
     *     description="Crée une nouvelle chambre avec des images dans un hôtel spécifique.",
     *     operationId="storeChambre",
     *     tags={"Chambre"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="num",
     *         in="query",
     *         required=true,
     *         description="Numéro de la chambre",
     *         @OA\Schema(type="string", example="101")
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         required=false,
     *         description="Description de la chambre",
     *         @OA\Schema(type="string", example="Une belle chambre avec vue sur la mer")
     *     ),
     *     @OA\Parameter(
     *         name="hotel_id",
     *         in="query",
     *         required=true,
     *         description="Identifiant de l'hôtel (doit exister dans la table `hotels`)",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="users_id",
     *         in="query",
     *         required=true,
     *         description="Identifiant de l'utilisateur associé (doit exister dans la table `users`)",
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Parameter(
     *         name="types_chambres_id",
     *         in="query",
     *         required=true,
     *         description="Identifiant du type de chambre (doit exister dans la table `types_chambres`)",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="images",
     *         in="query",
     *         required=true,
     *         description="Liste des images de la chambre à uploader",
     *         @OA\Schema(type="array", @OA\Items(type="string", format="binary", example="room_image.jpg"))
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="images", type="array", description="Liste des images de la chambre à uploader",
     *                 @OA\Items(type="string", format="binary", description="Image fichier", example="room_image.jpg")
     *             ),
     *             @OA\Property(property="num", type="string", example="101", description="Numéro de la chambre"),
     *             @OA\Property(property="description", type="string", example="Une belle chambre avec vue sur la mer", description="Description de la chambre"),
     *             @OA\Property(property="hotel_id", type="integer", example=1, description="Identifiant de l'hôtel (doit exister dans la table `hotels`)"),
     *             @OA\Property(property="users_id", type="integer", example=2, description="Identifiant de l'utilisateur associé (doit exister dans la table `users`)"),
     *             @OA\Property(property="types_chambres_id", type="integer", example=1, description="Identifiant du type de chambre (doit exister dans la table `types_chambres`)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Chambre créée avec succès.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Chambre enregistrer avec succès."),
     *             @OA\Property(property="room", type="object",
     *                 @OA\Property(property="num", type="string", example="101"),
     *                 @OA\Property(property="description", type="string", example="Une belle chambre avec vue sur la mer"),
     *                 @OA\Property(property="hotel_id", type="integer", example=1),
     *                 @OA\Property(property="users_id", type="integer", example=2),
     *                 @OA\Property(property="types_chambres_id", type="integer", example=1),
     *                 @OA\Property(property="statut", type="string", example="AVAILABLE"),
     *             ),
     *             @OA\Property(property="images", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="image_path", type="string", example="images/Chambre/Room_101_1688156037_1234.jpg")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Erreur lors de la validation ou des données.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Chambre déjà existante ou images non uploadées.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Accès Refusé")
     *         )
     *     )
     * )
     */



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
            ],404);
        }
        if (!$request->hasFile('images')){
            return response()->json([
                'error'=>true,
                'message' => 'Images non uploader.',
            ], 404);
        }
        // first of all let create the room
        $room = Chambre::create([
            "num"=>$request->num,
            "description"=>$request->description,
            "types_chambres_id"=>$request->types_chambres_id,
            "hotel_id"=>$user->hotels_id,
            "users_id"=>Auth::id(),
            "statut"=>ChambreStatus::MAINTENANCE,
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
                $fileName = "Room_".$request->num."_". time()."_".rand(1,1000).".".$image->getClientOriginalExtension();
                $filePath = $image->storeAs('images/Chambre',$fileName,'public');
                $room->chambreImage()->create(['image_path' => $filePath]);
            }
        }else{
            return response()->json([
                'error'=>true,
                'message' => 'Images non uploader.',
            ], 404);
        }
        $images = $room->chambreImage;
        return response()->json([
            'error'=>false,
            'message' => 'Chambre enregistrer avec succès.',
            'room' => $room,
            'images'=>$images,
        ], 200);
    }
// show all room
    /**
     * @OA\Get(
     *     path="/api/chambre/showAll",
     *     summary="Obtenir toutes les chambres pour l'hôtel de l'utilisateur authentifié",
     *     tags={"Chambre"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des chambres de votre hôtel",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Liste des chambres de votre hôtel"),
     *             @OA\Property(
     *                 property="chambres",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="num", type="string", example="101"),
     *                     @OA\Property(property="description", type="string", example="Chambre avec vue sur la mer"),
     *                     @OA\Property(property="statut", type="string", example="AVAILABLE"),
     *                     @OA\Property(property="images", type="array",
     *                         @OA\Items(type="string", format="binary")
     *                     ),
     *                     @OA\Property(property="type_chambre", type="string", example="Double"),
     *                     @OA\Property(property="hotel_id", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé pour ce rôle",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Accès Refusé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucune chambre disponible",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="No room disponible")
     *         )
     *     )
     * )
     */
    public function showAllRoom(): JsonResponse
    {
        $user = Auth::user();
        abort_if(!in_array($user->role, [UserRoles::ADMIN, UserRoles::FRONTDESKAGENT, UserRoles::SUDO]), 403, "Accès Refusé");
        $rooms = Chambre::query()
            ->where('statut',"!=",ChambreStatus::DELETED)
            ->where('hotel_id',$user->hotels_id)
            ->with('chambreImage')
            ->with('typesChambre')
            ->orderBy('created_at', 'DESC')
            ->get();
        if (!$rooms){
            return response()->json([
                "error"=>false,
                "message"=>"Aucune chambre disponible"
            ]);
        }
        return response()->json([
            "error"=>false,
            "message"=>"liste des chambres de votre hotel",
            "chambres"=>$rooms
        ]);
    }
// show specific room
    /**
     * @OA\Get(
     *     path="/api/chambre/show/{id:id}",
     *     summary="Obtenir les détails d'une chambre spécifique",
     *     tags={"Chambre"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la chambre à récupérer",
     *         @OA\Schema(type="integer")
     *     ),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Détails de la chambre spécifiée",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Chambre"),
     *             @OA\Property(
     *                 property="chambre",
     *                 type="object",
     *                 @OA\Property(property="num", type="string", example="101"),
     *                 @OA\Property(property="description", type="string", example="Chambre avec vue sur la mer"),
     *                 @OA\Property(property="statut", type="string", example="AVAILABLE"),
     *                 @OA\Property(property="hotel", type="object",
     *                     @OA\Property(property="name", type="string", example="Hotel Luxe")
     *                 ),
     *                 @OA\Property(property="typesChambre", type="object",
     *                     @OA\Property(property="name", type="string", example="Double")
     *                 ),
     *                 @OA\Property(property="chambreImage", type="array",
     *                     @OA\Items(type="string", format="binary")
     *                 ),
     *                 @OA\Property(property="tarifications", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="price", type="number", format="float", example=150),
     *                         @OA\Property(property="season", type="string", example="Summer")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Chambre inexistante",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Chambre inexistante")
     *         )
     *     )
     * )
     */
    public function showChambre($id): JsonResponse
    {
        //let find the Room
        $room = Chambre::query()->find($id);
        if (!$room){
            return response()->json([
                "error"=>false,
                "message"=>"chambre inexistante."
            ]);
        }
        $room->chambreImage;
        $room->hotel;
        $room->typesChambre->tarifications;
        return response()->json([
            "error"=>false,
            "message"=>"Chambre",
            "chambre"=>$room,
        ]);
    }
    // show all room with room's type and tarification
    /**
     * @OA\Get(
     *     path="/api/chambre/showAllRoomWithTarifs",
     *     summary="Obtenir toutes les chambres avec leurs tarifications",
     *     tags={"Chambre"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste de toutes les chambres avec leurs tarifications",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(
     *                 property="chambres",
     *                 type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="num", type="string", example="101"),
     *                     @OA\Property(property="description", type="string", example="Chambre avec vue sur la mer"),
     *                     @OA\Property(property="statut", type="string", example="AVAILABLE"),
     *                     @OA\Property(property="typesChambre", type="object",
     *                         @OA\Property(property="name", type="string", example="Double")
     *                     ),
     *                     @OA\Property(
     *                         property="tarifications",
     *                         type="array",
     *                         @OA\Items(type="object",
     *                             @OA\Property(property="price", type="number", format="float", example=150),
     *                             @OA\Property(property="season", type="string", example="Summer")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé pour les utilisateurs non-admin",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Accès Refusé")
     *         )
     *     )
     * )
     */
    public function showAllRoomWithTarifs(): JsonResponse
    {
        $user = Auth::user();
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");
        // let get all room with all data
        $rooms = Chambre::query()
            ->where('statut',"!=",ChambreStatus::DELETED)
            ->with('typesChambre.tarifications')
            ->orderBy('created_at', 'DESC')
            ->get();
        return response()->json([
            "error"=>false,
            "chambres"=>$rooms
        ],200);
    }
    //show room with tarifs
    /**
     * @OA\Get(
     *     path="/api/chambre/showRoomWithTarifs/{id:id}",
     *     summary="Obtenir une chambre avec ses tarifications",
     *     tags={"Chambre"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la chambre",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de la chambre avec ses tarifications",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(
     *                 property="chambres",
     *                 type="object",
     *                 @OA\Property(property="num", type="string", example="101"),
     *                 @OA\Property(property="description", type="string", example="Chambre avec vue sur la mer"),
     *                 @OA\Property(property="statut", type="string", example="AVAILABLE"),
     *                 @OA\Property(property="typesChambre", type="object",
     *                     @OA\Property(property="name", type="string", example="Double")
     *                 ),
     *                 @OA\Property(
     *                     property="tarifications",
     *                     type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="price", type="number", format="float", example=150),
     *                         @OA\Property(property="season", type="string", example="Summer")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé pour les utilisateurs non-admin",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Accès Refusé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Chambre non trouvée",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Chambre inexistante.")
     *         )
     *     )
     * )
     */
    public function showRoomWithTarifs($id)
    {
        $user = Auth::user();
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");
        $rooms = Chambre::query()
            ->where('statut',"!=",ChambreStatus::DELETED)
            ->with('typesChambre.tarifications')
            ->orderBy('created_at', 'DESC')
            ->findOrFail($id);
        return response()->json([
            "error"=>false,
            "chambres"=>$rooms
        ],200);
    }
    /**
     * @OA\Get(
     *     path="/api/chambre/showRoomWithPriceSeason",
     *     summary="Obtenir les chambres disponibles avec leurs tarifications actuelles en fonction de la saison",
     *     tags={"Chambre"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des chambres avec leurs tarifs pour la saison actuelle",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(
     *                 property="chambres",
     *                 type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="num", type="string", example="101"),
     *                     @OA\Property(property="description", type="string", example="Chambre avec vue sur la mer"),
     *                     @OA\Property(property="statut", type="string", example="AVAILABLE"),
     *                     @OA\Property(property="typesChambre", type="object",
     *                         @OA\Property(property="name", type="string", example="Double")
     *                     ),
     *                     @OA\Property(
     *                         property="tarifications",
     *                         type="array",
     *                         @OA\Items(type="object",
     *                             @OA\Property(property="price", type="number", format="float", example=150),
     *                             @OA\Property(property="season", type="string", example="Summer")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé pour les utilisateurs non-admin",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Accès Refusé")
     *         )
     *     )
     * )
     */
    public function showRoomWithPriceSeason(): JsonResponse
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
            }])
            ->orderBy('created_at', 'DESC')
            ->get();
        return response()->json([
            "error"=>false,
            "chambres"=>$rooms
        ],200);
    }
    /**
     * @OA\Get(
     *     path="/api/chambre/showSRoomWithPriceSeason/{id:id}",
     *     summary="Obtenir une chambre spécifique avec ses tarifs en fonction de la saison",
     *     tags={"Chambre"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la chambre",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de la chambre avec ses tarifs pour la saison actuelle",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(
     *                 property="chambres",
     *                 type="object",
     *                 @OA\Property(property="num", type="string", example="101"),
     *                 @OA\Property(property="description", type="string", example="Chambre avec vue sur la mer"),
     *                 @OA\Property(property="statut", type="string", example="AVAILABLE"),
     *                 @OA\Property(property="typesChambre", type="object",
     *                     @OA\Property(property="name", type="string", example="Double")
     *                 ),
     *                 @OA\Property(
     *                     property="tarifications",
     *                     type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="price", type="number", format="float", example=150),
     *                         @OA\Property(property="season", type="string", example="Summer")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé pour les utilisateurs non-admin",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Accès Refusé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Chambre non trouvée",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Chambre inexistante.")
     *         )
     *     )
     * )
     */
    public function showSRoomWithPriceSeason($id): JsonResponse
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
    /**
     * @OA\Post(
     *     path="/api/chambre/searchRoom",
     *     summary="Obtenir les chambres disponibles dans une période spécifiée",
     *     tags={"Chambre"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="date_deb", type="string", format="date", example="2024-12-01"),
     *             @OA\Property(property="date_fin", type="string", format="date", example="2024-12-10"),
     *             @OA\Property(property="nmb_per", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des chambres disponibles",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(
     *                 property="available_rooms",
     *                 type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="num", type="string", example="101"),
     *                     @OA\Property(property="description", type="string", example="Chambre avec vue sur la mer"),
     *                     @OA\Property(property="tarif_total", type="number", format="float", example=500),
     *                     @OA\Property(property="nuit", type="integer", example=9),
     *                     @OA\Property(property="tarif", type="number", format="float", example=50),
     *                     @OA\Property(property="nmb_per", type="integer", example=2),
     *                     @OA\Property(property="dateDebR", type="string", format="date", example="2024-12-01"),
     *                     @OA\Property(property="dateFinR", type="string", format="date", example="2024-12-10"),
     *                     @OA\Property(
     *                         property="typesChambre",
     *                         type="object",
     *                         @OA\Property(property="capacity", type="integer", example=2)
     *                     ),
     *                     @OA\Property(
     *                         property="chambreImage",
     *                         type="array",
     *                         @OA\Items(type="object",
     *                             @OA\Property(property="image_path", type="string", example="images/room_101.jpg")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Paramètres d'entrée invalides",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Les dates de réservation sont invalides.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé pour les utilisateurs non administrateurs",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Accès Refusé")
     *         )
     *     )
     * )
     */
    public function getAvailableRooms(AvailableRoomRequest $request): JsonResponse
    {

        $validatorData = $request->validated();
        $dateDeb = Carbon::parse($request->date_deb);
        $dateFin = Carbon::parse($request->date_fin);
        $dayMonthDeb = $dateDeb->format('d-m');
        $dayMonthFin = $dateFin->format('d-m');
        $nmb_per = $request->nmb_per;

        // Fetch rooms that are not reserved in the specified date range
        $availableRooms = Chambre::query()
            ->where('statut',ChambreStatus::AVAILABLE)
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
            ->with('chambreImage')
            ->get();

        // Calculate the total tariff for each room based on the number of nights
        $availableRooms->each(function ($room) use ($dateDeb, $dateFin,$nmb_per) {
            $numberOfNights = $dateFin->diffInDays($dateDeb);
            if ($room->typesChambre && $room->typesChambre->tarifications->isNotEmpty()) {
                $tarifApplicable = $room->typesChambre->tarifications->first()->prix * $numberOfNights;
                $room->tarif_total = $tarifApplicable;
                $room->nuit = $numberOfNights;
                $room->tarif = $room->typesChambre->tarifications->first()->prix;
                $room->dateDebR = $dateDeb;
                $room->dateFinR = $dateFin;
                $room->nmb_per = $nmb_per;
            } else {
                $room->tarif_applicable = null; // Set null if no applicable tariff found
            }
        });

        return response()->json([
            "error" => false,
            "available_rooms" => $availableRooms,
        ], 200);
    }

    public function roomMaintenance($id): JsonResponse
    {
        //dd($id);
        //let find element
        $typeRoom = Chambre::query()->find($id);

        // Vérifier si l'élément existe
        if (!$typeRoom) {
            return response()->json([
                "error" => true,
                "message" => "Chambre introuvable. ",
            ], 404);
        }
        $typeRoom->update([
            'statut'=>ChambreStatus::MAINTENANCE,
        ]);
        return response()->json([
            "error"=>false,
            "message"=>"Chambre en maintenance."
        ]);
    }
    public function roomAvailable($id): JsonResponse
    {
        //dd($id);
        //let find element
        $typeRoom = Chambre::query()->find($id);
        // Vérifier si l'élément existe
        if (!$typeRoom) {
            return response()->json([
                "error" => true,
                "message" => "Chambre introuvable. ",
            ], 404);
        }

        $tarification = $typeRoom->typesChambre->tarifications;
        if ($tarification->isEmpty()){
            return response()->json([
                "error" => true,
                "message" => "Aucune tarification disponible veuillez attribuer des tarifications au type de chambre. ",
            ], 404);
        }
        $typeRoom->update([
            'statut'=>ChambreStatus::AVAILABLE,
        ]);
        return response()->json([
            "error"=>false,
            "message"=>"Chambre disponible."
        ]);
    }
}
