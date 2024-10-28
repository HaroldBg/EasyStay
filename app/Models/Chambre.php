<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chambre extends Model
{
    use HasFactory;

    protected $fillable = [
        "num",
        "description",
        "types_id",
    ];

    protected $casts = [
        'createdAt' => 'datetime',
        'updateAt' => 'datetime',
    ];

    public function typesChambre(): BelongsTo
    {
        return $this->belongsTo(TypesChambre::class);
    }
}
