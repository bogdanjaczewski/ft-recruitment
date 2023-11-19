<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Duel;

class AppTest extends TestCase
{
    public function test_get_user_data()
    {
        $user = User::inRandomOrder()->first();
        $this->actingAs($user)->get('/api/user-data')->assertJson(['id' => $user->id]);

    }
    
    public function test_user_can_start_duel()
    {
        $user = User::inRandomOrder()->first();
        
        $activeDuel = Duel::where(function ($query) use ($user) {
            $query->where('user1_id', $user->id)
                ->orWhere('user2_id', $user->id);
        })
        ->where('active', true)
        ->first();
        
        $opponent = User::where('id', '!=', $user->id)
         ->where('level', $user->level)
         ->inRandomOrder()
         ->first();

        $response = $this->actingAs($user)->postJson('/api/duels');
        if($activeDuel) {
            $response->assertJson(['message' => 'Player is already in an active duel.', 'duel' => $activeDuel->toArray()]);
        } elseif($opponent) {
            $response->assertJsonStructure(['message', 'duel_id']);
            $duelId = $response->json('duel_id');
            $duel = Duel::find($duelId);
            $this->assertTrue($duel->user1_id === $user->id ||$duel->user2_id === $user->id);
            $this->assertNotNull($duel->user2_id);
            $this->assertNotNull($duel->user1_id);
        } else {
            $response->assertJson(['error' => 'No valid opponent found for the duel.']); 
        }      
    }

    public function test_user_can_get_current_duel_data()
    {
        $user = User::inRandomOrder()->first();
        
        $duel = Duel::where(function ($query) use ($user) {
                $query->where('user1_id', $user->id)
                    ->orWhere('user2_id', $user->id);
            })
            ->latest()
            ->first();
            
        $response = $this->actingAs($user)->getJson('/api/duels/active');
        
        if(!$duel) {
           $response->assertStatus(404);  
        } else {
            $response->assertJsonStructure(['round', 'your_points', 'opponent_points', 'status', 'cards']);

            $this->assertEquals($duel->round, $response->json('round'));
            $this->assertEquals($user->id == $duel->user1_id ? $duel->user_1_points : $duel->user_2_points, $response->json('your_points'));
            $this->assertEquals($user->id == $duel->user1_id ? $duel->user_2_points : $duel->user_1_points, $response->json('opponent_points'));
            $this->assertEquals($duel->active ? 'active' : 'finished', $response->json('status'));

            $response->assertJsonStructure(['cards' => []]); 
        }
    }

    public function test_user_can_draw_card()
    {
        $user = User::inRandomOrder()->first();
        $eligible = $user->isPlayerEligibleToDraw();
        $response = $this->actingAs($user)->postJson('/api/cards');

        if (!$eligible) {
            $response->assertStatus(403); 
        } else {
            $response->assertSuccessful();
            $response->assertJsonStructure(['message', 'card']);
        }
    }

    public function test_user_can_get_user_data()
    {
        $user = User::inRandomOrder()->first();

        $response = $this->actingAs($user)->getJson('/api/user-data');

        $response->assertSuccessful();
        
    }


    public function test_user_can_get_duel_history()
    {
        $user = User::inRandomOrder()->first();

        $response = $this->actingAs($user)->getJson('/api/duels');

        $response->assertSuccessful();
        $duelHistory = $response->json();


        foreach ($duelHistory as $duel) {
            $this->assertArrayHasKey('id', $duel);
            $this->assertArrayHasKey('player_name', $duel);
            $this->assertArrayHasKey('opponent_name', $duel);
            $this->assertArrayHasKey('won', $duel);

            $this->assertIsInt($duel['id']);
            $this->assertIsString($duel['player_name']);
            $this->assertIsString($duel['opponent_name']);
            $this->assertIsInt($duel['won']);
        }
    }
}
