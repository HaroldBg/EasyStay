<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChambreImage extends Model
{
    use HasFactory;
    protected $fillable = [
        "chambre_id",
        "image_path",
    ];

    public function chambre():BelongsTo
    {
        return $this->belongsTo(Chambre::class);
    }
}
