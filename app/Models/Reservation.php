<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;
    protected $fillable = [
        "user_id",
        "email",
        "chambre_id",
        "date_deb",
        "date_fin",
        "status",
        "nmb_per",
        "tarif_app",
    ];

    protected $casts = [
        'date_deb' => 'date',
        'date_fin' => 'date',
        'createdAt' => 'datetime',
        'updateAt' => 'datetime',
        'status'=>ReservationStatus::class,
    ];

    public function user() :BelongsTo
    {
        return $this->belongsTo(User::class,"user_id");
    }

    // Relation avec la chambre
    public function chambre() : BelongsTo
    {
        return $this->belongsTo(Chambre::class,"chambre_id");
    }
}
