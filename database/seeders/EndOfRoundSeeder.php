<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EndOfRoundSeeder extends Seeder
{
    public $cards = [
        '2_of_clubs',
        '2_of_diamonds',
        '2_of_hearts',
        '2_of_spades',
        '3_of_clubs',
        '3_of_diamonds',
        '3_of_hearts',
        '3_of_spades',
        '4_of_clubs',
        '4_of_diamonds',
        '4_of_hearts',
        '4_of_spades',
        '5_of_clubs',
        '5_of_diamonds',
        '5_of_hearts',
        '5_of_spades',
        '6_of_clubs',
        '6_of_diamonds',
        '6_of_hearts',
        '6_of_spades',
        '7_of_clubs',
        '7_of_diamonds',
        '7_of_hearts',
        '7_of_spades',
        '8_of_clubs',
        '8_of_diamonds',
        '8_of_hearts',
        '8_of_spades',
        '9_of_clubs',
        '9_of_diamonds',
        '9_of_hearts',
        '9_of_spades',
        '10_of_clubs',
        '10_of_diamonds',
        '10_of_hearts',
        '10_of_spades',
        '11_of_clubs',
        '11_of_diamonds',
        '11_of_hearts',
        '11_of_spades',
        '12_of_clubs',
        '12_of_diamonds',
        '12_of_hearts',
        '12_of_spades',
        '13_of_clubs',
        '13_of_diamonds',
        '13_of_hearts',
        '13_of_spades',
        '14_of_clubs',
        '14_of_diamonds',
        '14_of_hearts',
        '14_of_spades'
    ];

    public $suits = [
        'hearts',
        'spades',
        'diamonds',
        'clubs'
    ];
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $random_cards=array_rand($this->cards,4);

        $round_id = DB::table('rounds')->insertGetId([
            'room_id' => 2,
            'player_1_cards' => json_encode([$this->cards[$random_cards[0]]]),
            'player_2_cards' => json_encode([$this->cards[$random_cards[1]]]),
            'player_3_cards' => json_encode([$this->cards[$random_cards[2]]]),
            'player_4_cards' => json_encode([$this->cards[$random_cards[3]]]),
            'turn' => 13,
            'team_1_score' => 6,
            'team_2_score' => 6,
            'dealer' => 0,
            'tarnib' => $this->suits[array_rand($this->suits,1)],
            'goal' => 7,
            'bids_data' => json_encode([
                'bids' => [-1,-1,-1,7],
                'current_bidder' => 3
            ]),
            'current_play' => json_encode(['','','','']),
            'player_turn' => rand(0,3)
        ]);

        DB::table('rooms')->where('id', 2)->update([
            'round_id' => $round_id
        ]);
    }
}
