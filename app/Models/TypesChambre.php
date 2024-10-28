<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypesChambre extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "capacity",
        "features",
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
}
