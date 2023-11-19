<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Duel extends Model
{
    use HasFactory;

    protected $fillable = ['user1_id', 'user2_id', 'winner_id', 'round'];

    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }
    
     public function cards()
    {
        return $this->belongsToMany(Card::class, 'card_duel')->withTimestamps();
    }
}