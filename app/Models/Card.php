<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'name',
        'power',
        'image',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
    
    public function duels()
    {
        return $this->belongsToMany(Duel::class)->withTimestamps();
    }
}
