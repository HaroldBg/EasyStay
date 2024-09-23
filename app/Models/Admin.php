<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Admin extends User
{

    public function hotels() : HasOne
    {
        return $this->hasOne(Hotel::class);
    }
}
