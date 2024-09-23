<?php

namespace App\Models;

use App\Enums\HotelStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends Model
{
    use HasFactory;
    protected $fillable = [
        "nom",
        "email",
        "adresse",
        "tel",
        "logo",
        "etoile",
        "status",
        "users_id",
    ];

    protected $casts = [
        "status" => HotelStatus::class,
        "email_verified_at"=>"datetime",
    ];
    public function user() : HasMany
    {
        return $this->hasMany(User::class);
    }
    public  function admin() : BelongsTo
    {
        return $this->belongsTo(Admin::class,'users_id');
    }
}
