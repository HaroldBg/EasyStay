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
    /**
     * @OA\Post(
     *     path="/api/chambre/type/store",
     *     summary="Créer un type de chambre",
     *     tags={"Type chambre"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Nom du type de chambre",
     *         @OA\Schema(type="string", example="Chambre Standard")
     *     ),
     *     @OA\Parameter(
     *         name="capacity",
     *         in="query",
     *         required=true,
     *         description="Capacité d'accueil de la chambre",
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Parameter(
     *         name="features",
     *         in="query",
     *         required=false,
     *         description="Caractéristiques supplémentaires de la chambre",
     *         @OA\Schema(type="string", example="Vue sur la mer, Lit King Size")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Chambre Standard"),
     *             @OA\Property(property="capacity", type="integer", example=2),
     *             @OA\Property(property="features", type="string", example="Vue sur la mer, Lit King Size")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Le type de chambre a été créé avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Type de chambre créé avec succès"),
     *             @OA\Property(
     *                 property="type_chambre",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Chambre Standard"),
     *                 @OA\Property(property="capacity", type="integer", example=2),
     *                 @OA\Property(property="features", type="string", example="Vue sur la mer, Lit King Size"),
     *                 @OA\Property(property="status", type="string", example="AVAILABLE")
     *             )
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
     *     ),
     *     @OA\Response(
     *         response=1062,
     *         description="Type de chambre déjà existant",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Type de chambre existant.")
     *         )
     *     )
     * )
     */

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
    //showTypes of specific hotel
    /**
     * @OA\Get(
     *     path="/api/chambre/type/show",
     *     summary="Afficher les types de chambre",
     *     tags={"Type chambre"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des types de chambre de l'hôtel",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Liste des types de chambre de votre hôtel"),
     *             @OA\Property(
     *                 property="typeChambre",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string", example="Chambre Standard"),
     *                     @OA\Property(property="capacity", type="integer", example=2),
     *                     @OA\Property(property="features", type="string", example="Vue sur la mer, Lit King Size"),
     *                     @OA\Property(property="status", type="string", example="AVAILABLE")
     *                 )
     *             )
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
    public function showTypes()
    {
        $user = Auth::user();
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");
        $typeRoom = TypesChambre::query()
            ->where('hotel_id',$user->hotels_id)
            ->where('status',"!=",TypeChambreStatus::DELETED)
            ->orderBy('created_at', 'desc')
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
    //show all types
    /**
     * @OA\Get(
     *     path="/api/chambre/type/showAll",
     *     summary="Afficher tous les types de chambre",
     *     tags={"Type chambre"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste de tous les types de chambre",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Liste des types de chambre."),
     *             @OA\Property(
     *                 property="typeChambre",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string", example="Chambre Standard"),
     *                     @OA\Property(property="capacity", type="integer", example=2),
     *                     @OA\Property(property="features", type="string", example="Vue sur la mer, Lit King Size"),
     *                     @OA\Property(property="status", type="string", example="AVAILABLE")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé pour les utilisateurs non administrateurs ou sudo",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Accès Refusé")
     *         )
     *     )
     * )
     */
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


    /**
     * @OA\Put(
     *     path="/api/chambre/type/store",
     *     summary="Créer un type de chambre",
     *     tags={"Type chambre"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="L'ID du type de chambre à mettre à jour.",
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Chambre Standard"),
     *             @OA\Property(property="capacity", type="integer", example=2),
     *             @OA\Property(property="features", type="string", example="Vue sur la mer, Lit King Size")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Le type de chambre a été créé avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Type de chambre créé avec succès"),
     *             @OA\Property(
     *                 property="type_chambre",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Chambre Standard"),
     *                 @OA\Property(property="capacity", type="integer", example=2),
     *                 @OA\Property(property="features", type="string", example="Vue sur la mer, Lit King Size"),
     *                 @OA\Property(property="status", type="string", example="AVAILABLE")
     *             )
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
     *     ),
     *     @OA\Response(
     *         response=1062,
     *         description="Type de chambre déjà existant",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Type de chambre existant.")
     *         )
     *     )
     * )
     */

    public function updateRoomType(StoreRoomTypeRequest $request)
    {
        $user = Auth::user();
        abort_if($user->role !== UserRoles::ADMIN, 403, "Accès Refusé");
        $validatorData = $request->validated();

        // Check if the room type exists by ID and belongs to the current user's hotel
        $typeRoom = TypesChambre::query()
            ->where('id', $request->id)
            ->where('hotel_id', $user->hotels_id)
            ->first();

        // If no room type found, return an error message
        if (!$typeRoom) {
            return response()->json([
                "error" => true,
                "message" => "Type de chambre introuvable ou n'appartient pas à votre hôtel."
            ], 404);
        }

        // Update the room type details
        $typeRoom->update([
            "name" => $request->name,
            "capacity" => $request->capacity,
            "features" => $request->features,
            "status" => $request->status ?? $typeRoom->status, // Keep the existing status if not provided
        ]);

        return response()->json([
            "error" => false,
            "message" => "Type de chambre mis à jour avec succès.",
            "type_chambre" => $typeRoom
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/chambre/delete/{id:id}",
     *     summary="Supprimer un type de chambre",
     *     tags={"Type chambre"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du type de chambre à supprimer",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Type de chambre supprimé avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Type de chambre supprimé avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de chambre non trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Type de chambre introuvable.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé pour les utilisateurs non administrateurs ou sudo",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Accès Refusé")
     *         )
     *     )
     * )
     */

    public function delete($id)
    {
        //dd($id);
        //let find element
        $typeRoom = TypesChambre::query()->find($id);

        // Vérifier si l'élément existe
        if (!$typeRoom) {
            return response()->json([
                "error" => true,
                "message" => "Type de chambre introuvable. ",
            ], 404);
        }
        $typeRoom->update([
            'status'=>TypeChambreStatus::DELETED,
        ]);
        return response()->json([
            "error"=>false,
            "message"=>"Type de chambre supprimé avec succès."
        ]);
    }

    public function show($id)
    {
        //dd($id);
        //let find element
        $typeRoom = TypesChambre::query()->find($id);

        // Vérifier si l'élément existe
        if (!$typeRoom) {
            return response()->json([
                "error" => true,
                "message" => "Type de chambre introuvable. ",
            ], 404);
        }
        return response()->json([
            "error"=>false,
            "typeRoom"=>$typeRoom,
        ]);
    }
}
