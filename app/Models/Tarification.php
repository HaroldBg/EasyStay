<?php

namespace App\Models;

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
    ];

    protected $casts = [
        "date_deb"=>"date",
        "date_fin"=>"date",
        'createdAt' => 'datetime',
        'updateAt' => 'datetime',
    ];
    public function typeChambre(): BelongsTo
    {
        return $this->belongsTo(TypesChambre::class,'types_chambres_id');
    }
}
