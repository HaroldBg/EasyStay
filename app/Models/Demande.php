<?php

namespace App\Models;

use App\Enums\DemandeSatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Demande extends Model
{
    use HasFactory;
    protected $fillable = [
        "motif",
        "nom",
        "adresse",
        "email",
        "users_id",
        "status"
    ];
    protected $casts = [
        'createdAt' => 'datetime',
        'updateAt' => 'datetime',
        "status"=>DemandeSatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'users_id');
    }
}
