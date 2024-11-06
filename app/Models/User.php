<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRoles;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'adresse',
        'tel',
        'picture',
        'password',
        'role',
        'status',
        'hotels_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'createdAt' => 'datetime',
        'updateAt' => 'datetime',
        'role' => UserRoles::class,
        'status' => UserStatus::class,
    ];

    public function getRole()
    {
        return $this->role;
    }
    public function demands(): HasMany
    {
        return $this->hasMany(Demande::class,'users_id');
    }
    public function hotel() : BelongsTo
    {
        return $this->belongsTo(Hotel::class,'hotels_id');
    }
    public function reservations():HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
