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
            ->get();

        return response()->json($reservations);
    }
}
