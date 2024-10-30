<?php

namespace App\Models;

use App\Enums\HotelStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypesChambre extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "capacity",
        "features",
        "hotel_id",
        "users_id",
        "status",
    ];

    protected $casts = [
        'createdAt' => 'datetime',
        'updateAt' => 'datetime',
    ];

    public function chambres(): HasMany
    {
        return $this->hasMany(Chambre::class);
    }

    public function tarifications(): HasMany
    {
        return $this->hasMany(Tarification::class);
    }
    // hotel
    public function hotel():BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}
