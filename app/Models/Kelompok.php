<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany; 

class Kelompok extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'district',
        'city',
        'address',
    ];

    /**
     * Get the users for the kelompok.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the generuses for the kelompok.
     */
    public function generuses(): HasMany
    {
        return $this->hasMany(Generus::class);
    }
}
