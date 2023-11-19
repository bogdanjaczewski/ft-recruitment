<?php

namespace App\Http\Controllers\Api;

use App\Models\Duel;
use App\Models\User;
use App\Models\Card;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class DuelController extends Controller
{
    public function startDuel()
    {
        $user = Auth::user();
        
        $activeDuel = Duel::where(function ($query) use ($user) {
            $query->where('user1_id', $user->id)
                ->orWhere('user2_id', $user->id);
        })
        ->where('active', true)
        ->first();

        if ($activeDuel) {
            return ['message' => 'Player is already in an active duel.', 'duel' => $activeDuel];
        }

        $opponent = User::where('id', '!=', $user->id)
            ->where('level', $user->level)
            ->inRandomOrder()
            ->first();

        if (!$opponent) {
            return response(['error' => 'No valid opponent found for the duel.'], 404);
        }

        $duel = new Duel;
        $duel->user1_id = $user->id;
        $duel->user2_id = $opponent->id;
        $duel->save();
        

        return ['message' => 'Duel started successfully.', 'duel_id' => $duel->id];
    }
    
    public function selectCard(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        
        $selectedCard = Card::find($request['id']);
        $duel = Duel::where(function ($query) use ($user) {
                $query->where('user1_id', $user->id)
                    ->orWhere('user2_id', $user->id);
            })
            ->where('active', true)
            ->first();

        if (!$duel) {
            return response(['error' => 'No active duel found for the player.'], 404);
        }
        
        
        if($duel->user1_id == $user->id) {
            $opponent = User::find($duel->user2_id);
        } else {
            $opponent = User::find($duel->user1_id);
        }

        $opponentCard = $this->getRandomEligibleCard($opponent, $duel->cards);

        $playerPoints = $selectedCard->power;
        $opponentPoints = $opponentCard->power;

        $duel->cards()->attach([
            $selectedCard->id => [
                'user_id' => $user->id,
            ],
            $opponentCard->id => [
                'user_id' => $opponent->id
            ]
        ]);
        
        $playerColumn = $duel->user1_id === $user->id ? 'user_1_points' : 'user_2_points';
        $opponentColumn = $duel->user1_id === $user->id ? 'user_2_points' : 'user_1_points';
        
        if ($selectedCard->power >= $opponentCard->power) {
            $duel->$playerColumn++;
        } else {
            $duel->$opponentColumn++;
        }
        
        $duel->round++;
        
        if ($duel->round > 5) {

            $winner = $this->determineWinner($duel);

            $duel->active = false;
            $duel->winner_id = $winner->id;

            $this->handleLevelPoints($winner);
        }
        
        $duel->save();

        return [
            'message' => 'Card selected and points calculated successfully.',
            'player_points' => $playerPoints,
            'opponent_points' => $opponentPoints,
            'duel' => $duel->fresh(), 
        ];
    }

    private function getRandomEligibleCard(User $player, $usedCards)
    {
        $eligibleCards = $player->cards()->whereNotIn('id', $usedCards->pluck('id')->toArray())->get();
       
        return $eligibleCards->random();
    }
    
    public function getCurrentDuelData()
    {
        $user = Auth::user();
        $duel = Duel::where(function ($query) use ($user) {
                $query->where('user1_id', $user->id)
                    ->orWhere('user2_id', $user->id);
            })
            ->latest()
            ->first();
            
        if (!$duel) {
            return response(['error' => 'No  duel found for the player.'], 404);
        }

         return [
            'round' => $duel->round,
            'your_points' => $user->id == $duel->user1_id ? $duel->user_1_points : $duel->user_2_points,
            'opponent_points' => $user->id == $duel->user1_id ? $duel->user_2_points : $duel->user_1_points,
            'status' => $duel->active ? 'active' : 'finished',
            'cards' => $user->cards()->get(),
        ];
         
    }

    private function determineWinner(Duel $duel)
    {
        return $duel->user_1_points >= $duel->user_2_points ? $duel->user1 : $duel->user2;
    }

    private function handleLevelPoints($winner)
    {
        $winner->level_points += 20;
        $winner->level = $winner->level_points >= 160 ? 3 : ($winner->level_points >= 100 ? 2 : 1);
        $winner->save();
    }
    
    public function getDuelHistory()
    {
        $user = Auth::user();
        $duelHistory = Duel::where(function ($query) use ($user) {
                $query->where('user1_id', $user->id)
                    ->orWhere('user2_id', $user->id);
            })
            ->where('active', false)
            ->orderByDesc('created_at')
            ->get();

        $formattedHistory = $duelHistory->map(function ($duel) use ($user) {
        $opponentId = $duel->user1_id === $user->id ? $duel->user2_id : $duel->user1_id;
        $opponentName = User::find($opponentId)->username;
            return [
                'id' => $duel->id,
                'player_name' => $user->username,
                'opponent_name' => $opponentName,
                'won' => $this->didUserWin($user->id, $duel),
            ];
        });

        return $formattedHistory->toArray();
    }

    private function didUserWin($userId, Duel $duel)
    {
        return $duel->winner_id == $userId ? 1 : 0;
    }
}

