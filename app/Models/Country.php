<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    /**
     * The Countries that are mass assignable.
     *
     * @var array
     */
    public function state(): HasMany
    {
        return $this->hasMany(State::class, 'country_id');
    }

    public function address(): HasMany
    {
        return $this->hasMany(Address::class, 'country_id');
    }
}
