<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Card;

class PlayerSeeder extends Seeder
{
    public function run()
    {
        $this->createPlayers(1, 0, 5);
        $this->createPlayers(2, 100, 10);
        $this->createPlayers(3, 160, 15);
    }
    
    private function createPlayers($level, $levelPoints, $cardsCount)
    {
        User
            ::factory()
            ->count(2)
            ->create(['level' => $level, 'level_points' => $levelPoints])
            ->each(function ($player) use ($cardsCount) {
                // Get random card IDs not already belonging to the player
                $randomCardIds = Card::whereNotIn('id', $player->cards->pluck('id'))->inRandomOrder()->limit($cardsCount)->pluck('id');
                // Attach the randomly chosen cards to the player
                $player->cards()->attach($randomCardIds);
            })
        ;
    }
}
