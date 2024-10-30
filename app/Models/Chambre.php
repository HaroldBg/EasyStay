<?php

namespace App\Models;

use App\Enums\ChambreStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chambre extends Model
{
    use HasFactory;

    protected $fillable = [
        "num",
        "description",
        "types_chambres_id",
        "hotel_id",
        "users_id",
        "statut"
    ];

    protected $casts = [
        'createdAt' => 'datetime',
        'updateAt' => 'datetime',
        'statut'=>ChambreStatus::class,
    ];

    public function typesChambre(): BelongsTo
    {
        return $this->belongsTo(TypesChambre::class);
    }
    public function hotel():BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
    public function chambreImage(): HasMany
    {
        return $this->hasMany(ChambreImage::class);
    }
    public function tarification(): HasMany
    {
        return $this->hasMany(Tarification::class);
    }
}
