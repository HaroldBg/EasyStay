<?php

namespace App\Http\Controllers\Chambre;

use App\Enums\ReservationStatus;
use App\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chambre\StoreReservationRequest;
use App\Models\Chambre;
use App\Models\Reservation;
use App\Models\Tarification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    //let store reservation
    /**
     * @OA\Post(
     *     path="/api/reservation/storeReserv",
     *     summary="Créer une réservation pour une chambre",
     *     description="Cette API permet de créer une réservation pour une chambre en fonction des dates sélectionnées.",
     *     operationId="storeReservation",
     *     tags={"Reservation"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="chambre_id",
     *         in="query",
     *         description="Identifiant de la chambre à réserver",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Identifiant de l'utilisateur qui fait la réservation (facultatif si un email est fourni)",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             example=2
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Email de l'utilisateur (facultatif si un user_id est fourni)",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="user@example.com"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="date_deb",
     *         in="query",
     *         description="Date de début de la réservation (format : YYYY-MM-DD)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2024-11-25"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="date_fin",
     *         in="query",
     *         description="Date de fin de la réservation (format : YYYY-MM-DD)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2024-11-30"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="nmb_per",
     *         in="query",
     *         description="Nombre de personnes pour la réservation",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=2
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="chambre_id", type="integer", example=1, description="Identifiant de la chambre à réserver"),
     *             @OA\Property(property="user_id", type="integer", example=2, description="Identifiant de l'utilisateur qui fait la réservation (facultatif si un email est fourni)"),
     *             @OA\Property(property="email", type="string", example="user@example.com", description="Email de l'utilisateur (facultatif si un user_id est fourni)"),
     *             @OA\Property(property="date_deb", type="string", format="date", example="2024-11-25", description="Date de début de la réservation (format : YYYY-MM-DD)"),
     *             @OA\Property(property="date_fin", type="string", format="date", example="2024-11-30", description="Date de fin de la réservation (format : YYYY-MM-DD)"),
     *             @OA\Property(property="nmb_per", type="integer", example=2, description="Nombre de personnes pour la réservation"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Réservation créée avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Réservation réussie."),
     *             @OA\Property(property="reservation", type="object",
     *                 @OA\Property(property="user_id", type="integer", example=2),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="chambre_id", type="integer", example=1),
     *                 @OA\Property(property="date_deb", type="string", example="2024-11-25"),
     *                 @OA\Property(property="date_fin", type="string", example="2024-11-30"),
     *                 @OA\Property(property="status", type="string", example="CONFIRMED"),
     *                 @OA\Property(property="nmb_per", type="integer", example=2),
     *                 @OA\Property(property="tarif_app", type="number", format="float", example=500.0)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation ou disponibilité de la chambre",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="La chambre n'est pas disponible pour ces dates.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Chambre non trouvée",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Chambre non trouvée.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé (par exemple, pour un utilisateur non authentifié)",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Accès refusé")
     *         )
     *     )
     * )
     */

    public function storeReservation(StoreReservationRequest $request)
    {
        $validatorData = $request->validated();
        // let check if any reservation is in waitlist
        $chambre = Chambre::query()->find($request->chambre_id);
        $reservationsExistantes = Reservation::query()
            ->where('chambre_id', $chambre->id)
            ->where('status',"!=", [ReservationStatus::CANCELED,ReservationStatus::DELETED])
            ->where(function($query) use ($request) {
                $query->whereBetween('date_deb', [$request->date_deb, $request->date_fin])
                    ->orWhereBetween('date_fin', [$request->date_deb, $request->date_fin])
                    ->orWhere(function($query) use ($request) {
                        $query->where('date_deb', '<=', $request->date_deb)
                            ->where('date_fin', '>=', $request->date_fin);
                    });
            })
            ->exists();

        if ($reservationsExistantes) {
            return response()->json([
                'error' => true,
                'message' => 'La chambre n\'est pas disponible pour ces dates.',
            ], 400);
        }
        //let make sure that one of mail or user_id is set
        if (!$request->email && !$request->user_id){
            return response()->json([
                'error' => true,
                'message' => "Au moins l'un des deux champs doit être renseigner Email ou Utilisateur",
            ], 400);
        }
        // let get the number of nights
        $dateDeb = Carbon::parse($request->date_deb);
        $dateFin = Carbon::parse($request->date_fin);
        $numberOfNights = $dateFin->diffInDays($dateDeb);
        // let get day and month for the checking
        $dayMonthDeb = $dateDeb->format('d-m');
        $dayMonthFin = $dateFin->format('d-m');
        // let get type of the room
        $chambre = Chambre::query()->find($request->chambre_id);
        $typeChambreId = $chambre->typesChambre->id;
        // get the applicable tarif by date and room's type
        $tarification = Tarification::query()
            ->where('types_chambres_id', $typeChambreId)
            ->whereRaw("DATE_FORMAT(date_deb, '%d-%m') <= ?", [$dayMonthDeb])
            ->whereRaw("DATE_FORMAT(date_fin, '%d-%m') >= ?", [$dayMonthFin])
            ->first();
        // Calculer le tarif total
        //dd($tarif_app = $tarification ? $tarification->prix * $numberOfNights : 0);
        $tarif_app = $tarification ? $tarification->prix * $numberOfNights : 0;
        /*dd($tarif_app);*/
        // let insert reservation
        $reservation  = Reservation::create([
            "user_id"=>$request->user_id,
            'email' => $request->email,
            'chambre_id' => $request->chambre_id,
            'date_deb' => $request->date_deb,
            'date_fin' => $request->date_fin,
            'status' => ReservationStatus::CONFIRMED,
            'nmb_per' => $request->nmb_per,
            'tarif_app'=>$tarif_app,
        ]);
        return response()->json([
            'error' => false,
            'message' => 'Réservation réussie.',
            'reservation' => $reservation,
        ], 200);
    }

    // let show all reservation by user's hotel authentificated
    /**
     * @OA\Get(
     *     path="/api/reservation/getReserv",
     *     summary="Afficher les réservations d'un hôtel pour un utilisateur connecté",
     *     description="Cette API permet d'afficher les réservations liées à l'hôtel de l'utilisateur connecté (admin, agent de réception, ou sudo).",
     *     operationId="showReservationByHotelUser",
     *     tags={"Reservation"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des réservations associées à l'hôtel de l'utilisateur",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=2),
     *                 @OA\Property(property="chambre_id", type="integer", example=1),
     *                 @OA\Property(property="date_deb", type="string", format="date", example="2024-11-25"),
     *                 @OA\Property(property="date_fin", type="string", format="date", example="2024-11-30"),
     *                 @OA\Property(property="status", type="string", example="CONFIRMED"),
     *                 @OA\Property(property="nmb_per", type="integer", example=2),
     *                 @OA\Property(property="tarif_app", type="number", format="float", example=500.0),
     *                 @OA\Property(property="chambre", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Chambre Standard"),
     *                     @OA\Property(property="capacity", type="integer", example=2),
     *                     @OA\Property(property="features", type="string", example="Vue sur la mer, Lit King Size"),
     *                     @OA\Property(property="status", type="string", example="AVAILABLE")
     *                 ),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john.doe@example.com")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé pour les utilisateurs non autorisés",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Accès refusé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucune réservation trouvée pour cet hôtel",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Aucune réservation trouvée pour cet hôtel")
     *         )
     *     )
     * )
     */

    public function showReservationByHotelUser()
    {
        $user = Auth::user();
        abort_if(!in_array($user->role, [UserRoles::ADMIN, UserRoles::FRONTDESKAGENT, UserRoles::SUDO]), 403, "Accès Refusé");
        // Récupérer l'hôtel de l'utilisateur connecté
        $hotelId = $user->hotels_id;

        // Récupérer les réservations associées aux chambres de cet hôtel
        $reservations = Reservation::query()
            ->whereHas('chambre', function($query) use ($hotelId) {
                $query->where('hotel_id', $hotelId);
            })
            ->with(['chambre', 'user'])
            ->orderBy('created_at', 'DESC')
            ->get();

        return response()->json($reservations);
    }

    public function checkIn($id){
        $user = Auth::user();
        abort_if(!in_array($user->role, [UserRoles::ADMIN, UserRoles::FRONTDESKAGENT, UserRoles::SUDO]), 403, "Accès Refusé");
        $reservation = Reservation::query()->find($id);
        // Vérifier si l'élément existe
        if (!$reservation) {
            return response()->json([
                "error" => true,
                "message" => "Reservation introuvable. ",
            ], 404);
        }
        $reservation->update([
            'status'=>ReservationStatus::CHECKIN,
        ]);
        return response()->json([
            "error"=>false,
            "message"=>"Statut passer au check-in."
        ]);
    }

    public function checkOut($id){
        $user = Auth::user();
        abort_if(!in_array($user->role, [UserRoles::ADMIN, UserRoles::FRONTDESKAGENT, UserRoles::SUDO]), 403, "Accès Refusé");
        $reservation = Reservation::query()->find($id);
        // Vérifier si l'élément existe
        if (!$reservation) {
            return response()->json([
                "error" => true,
                "message" => "Reservation introuvable. ",
            ], 404);
        }
        $reservation->update([
            'status'=>ReservationStatus::CHECKOUT,
        ]);
        // make facture
        return response()->json([
            "error"=>false,
            "message"=>"Statut passer au check-out."
        ]);
    }
    public function deleteReserv($id){
        $user = Auth::user();
        abort_if(!in_array($user->role, [UserRoles::ADMIN, UserRoles::FRONTDESKAGENT, UserRoles::SUDO]), 403, "Accès Refusé");
        $reservation = Reservation::query()->find($id);
        // Vérifier si l'élément existe
        if (!$reservation) {
            return response()->json([
                "error" => true,
                "message" => "Reservation introuvable. ",
            ], 404);
        }
        $reservation->update([
            'status'=>ReservationStatus::DELETED,
        ]);
        // make facture
        return response()->json([
            "error"=>false,
            "message"=>"Statut passer au check-out."
        ]);
    }
}
