<?php

namespace App\Models;

use App\Enums\TarificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarification extends Model
{
    use HasFactory;

    protected $fillable = [
        "prix",
        "saison",
        "date_deb",
        "date_fin",
        "types_chambres_id",
        "users_id",
        "status",
    ];

    protected $casts = [
        "date_deb"=>"date",
        "date_fin"=>"date",
        'createdAt' => 'datetime',
        'updateAt' => 'datetime',
        "status" => TarificationStatus::class,
    ];
    public function typeChambre(): BelongsTo
    {
        return $this->belongsTo(TypesChambre::class,'types_chambres_id');
    }
}
