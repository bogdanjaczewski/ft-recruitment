<?php

namespace App\Http\Controllers\Api;

use App\Models\Card;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function userData()
    {
        $user = Auth::user();
        
        return [
            'id' => $user->id,
            'username' => $user->username,
            'level' => $user->level,
            'level_points' => $user->level_points,
            'cards' => $user->cards()->get(),
            'new_card_allowed' => $user->isPlayerEligibleToDraw(),
        ];
    }

    public function drawCard()
    {
        $user = Auth::user();
        if (!$user->isPlayerEligibleToDraw()) {
            return response(['error' => 'Player is not eligible to draw a card.'], 403);
        }
        
        $playerCards = $user->cards->pluck('id')->toArray();
        $randomCard = Card::whereNotIn('id', $playerCards)->inRandomOrder()->first();
        $user->cards()->attach($randomCard);

        return [
            'message' => 'Card drawn successfully.',
            'card' => $randomCard
        ];
    }
}