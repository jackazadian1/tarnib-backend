<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Broadcast;


class TarnibController extends Controller
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
        'jack_of_clubs',
        'jack_of_diamonds',
        'jack_of_hearts',
        'jack_of_spades',
        'queen_of_clubs',
        'queen_of_diamonds',
        'queen_of_hearts',
        'queen_of_spades',
        'king_of_clubs',
        'king_of_diamonds',
        'king_of_hearts',
        'king_of_spades',
        'ace_of_clubs',
        'ace_of_diamonds',
        'ace_of_hearts',
        'ace_of_spades'
    ];

    public function createRoom(Request $request){

        $room_id = $this->generateRandomString();
        $random_cards = $this->cards;
        shuffle($random_cards);

        $id = DB::table('rooms')->insertGetId([
            'room_id' => $room_id,
            'player_1' => $request->player_name,
            'team_1_score' => 0,
            'team_2_score' => 0,
            

        ]);

        $round_id = DB::table('rounds')->insertGetId([
            'room_id' => $id,
            'player_1_cards' => json_encode(array_slice($random_cards, 0, 13)),
            'player_2_cards' => json_encode(array_slice($random_cards, 13, 13)),
            'player_3_cards' => json_encode(array_slice($random_cards, 26, 13)),
            'player_4_cards' => json_encode(array_slice($random_cards, 39, 13)),
            'turn' => 1,
            'team_1_score' => 0,
            'team_2_score' => 0,
            'dealer' => 0,
            'bids_data' => json_encode([
                'bids' => [0,0,0,0],
                'current_bidder' => 1
            ])
        ]);

        DB::table('rooms')->where('id', $id)->update([
            'round_id' => $round_id
        ]);


        return response()->json(['success' => true, 'room_id' => $room_id, 'player_name' => $request->player_name]);

    }

    function joinRoom(Request $request){
        $room = DB::table('rooms')->where('room_id', $request->room_id)->first();
        $player_seat = '';
        if($room->player_1 == $request->player_name) $player_seat = 1;
        else if($room->player_2 == $request->player_name) $player_seat = 2;
        else if($room->player_3 == $request->player_name) $player_seat = 3;
        else if($room->player_4 == $request->player_name) $player_seat = 4;

        $round = DB::table('rounds')->where('id', $room->round_id)->first();
        if($player_seat != ''){
            if($player_seat == 1){
                $cards = json_decode($round->player_1_cards);
            }else if($player_seat == 2){
                $cards = json_decode($round->player_2_cards);
            }else if($player_seat == 3){
                $cards = json_decode($round->player_3_cards);
            }else if($player_seat == 4){
                $cards = json_decode($round->player_4_cards);
            }else{
                $cards = [];
            }
        }

        if($cards != []){
            $cards = $this->sortCards($cards);
        }

        // Broadcast::channel('game', function ($request) {
        //     return ['name' => $request->player_name];
        // });

        // Broadcast::event('game.message', [
        //     'user' => $request->player_name,
        //     'message' => 'test',
        // ])->toOthers();
        

        return view('game')->with([
            'player_name' => $request->player_name,
            'player_seat' => $player_seat,
            'room_id' => $room->room_id,
            'player_1' => $room->player_1,
            'player_2' => $room->player_2,
            'player_3' => $room->player_3,
            'player_4' => $room->player_4,
            'turn' => $round->turn,
            'cards' => $cards,
        ]);
    }

    function getRoundInfo(Request $request){
        $room = DB::table('rooms')->where('room_id', $request->room_id)->first();
        $round = DB::table('rounds')->where('id', $room->round_id)->first();

        $player_seat = '';
        if($room->player_1 == $request->player_name) $player_seat = 1;
        else if($room->player_2 == $request->player_name) $player_seat = 2;
        else if($room->player_3 == $request->player_name) $player_seat = 3;
        else if($room->player_4 == $request->player_name) $player_seat = 4;

        if($player_seat == 1){
            $cards = json_decode($round->player_1_cards);
        }else if($player_seat == 2){
            $cards = json_decode($round->player_2_cards);
        }else if($player_seat == 3){
            $cards = json_decode($round->player_3_cards);
        }else if($player_seat == 4){
            $cards = json_decode($round->player_4_cards);
        }else{
            $cards = [];
        }

        if($cards != []){
            $cards = $this->sortCards($cards);
        }

        return response()->json([
            'player_seat' => $player_seat,
            'room_id' => $room->room_id,
            'player_1' => $room->player_1,
            'player_2' => $room->player_2,
            'player_3' => $room->player_3,
            'player_4' => $room->player_4,
            'turn' => $round->turn,
            'cards' => $cards,
            'dealer' => $round->dealer,
            'tarnib' => $round->tarnib,
            'bids_data' => json_decode($round->bids_data)
        ]);
    }

    function chooseSeat(Request $request){
        $player_number = "player_".$request->seat;
        DB::table('rooms')->where('room_id', $request->room_id)->update([
            $player_number => $request->player_name
        ]);

        event(new \App\Events\TarnibChooseSeatEvent($request->seat, $request->player_name));

        return response()->json(['success' => true]);
    }

    function sortCards($cards){
        $spades = [];
        $hearts = [];
        $clubs = [];
        $diamonds = [];

        foreach ($cards as $card) {
            if(str_contains($card, 'spades')){
                $spades[] = $card;
            }elseif (str_contains($card, 'hearts')) {
                $hearts[] = $card;
            }elseif (str_contains($card, 'clubs')) {
                $clubs[] = $card;
            }else{
                $diamonds[] = $card;
            }
        }

        $spades = $this->sortSuit($spades, 'spades');
        $hearts = $this->sortSuit($hearts, 'hearts');
        $clubs = $this->sortSuit($clubs, 'clubs');
        $diamonds = $this->sortSuit($diamonds, 'diamonds');

        $res = array_merge($hearts,$spades);
        $res = array_merge($res, $diamonds);
        $res = array_merge($res, $clubs);
        return $res;
    }

    function sortSuit($cards, $suit){
        $suffix = "_of_$suit";

        
        for($i = 0; $i < count($cards); $i++){
            for($j = 0; $j < count($cards)-1; $j++){
                $val1 = str_replace($suffix, "",$cards[$j]);
                $val2 = str_replace($suffix, "",$cards[$j+1]);

                if($val2 == "ace"){
                    $temp = $cards[$j];
                    $cards[$j] = $cards[$j+1];
                    $cards[$j+1] = $temp;
                }elseif ($val2 == "king" && $val1 != "ace") {
                    $temp = $cards[$j];
                    $cards[$j] = $cards[$j+1];
                    $cards[$j+1] = $temp;
                }elseif ($val2 == "queen" && $val1 != "ace" && $val1 != "king") {
                    $temp = $cards[$j];
                    $cards[$j] = $cards[$j+1];
                    $cards[$j+1] = $temp;
                }elseif ($val2 == "jack" && $val1 != "ace" && $val1 != "king" && $val1 != "queen") {
                    $temp = $cards[$j];
                    $cards[$j] = $cards[$j+1];
                    $cards[$j+1] = $temp;
                }elseif ($val2 > $val1 && is_numeric($val1) && is_numeric($val2)) {
                    $temp = $cards[$j];
                    $cards[$j] = $cards[$j+1];
                    $cards[$j+1] = $temp;
                }
            }
        }

        return $cards;
    }


    function bid(Request $request){
        $round_id = DB::table('rooms')->where('room_id', $request->room_id)->first()->round_id;
        $round = DB::table('rounds')->where('id', $round_id)->select('bids_data')->first();
        $bids_data = json_decode($round->bids_data);
        //event(new \App\Events\TarnibChooseSeatEvent($request->seat, $request->player_name));
        if($request->player_index == $bids_data->current_bidder){
            $bids_data->bids[$request->player_index] = $request->amount;
            $next_bidder = $bids_data->current_bidder+1 == 4 ? 0 : $bids_data->current_bidder+1;
            while($bids_data->bids[$next_bidder] == -1) {
                $next_bidder++;
                if($next_bidder == 4) $next_bidder = 0;
            }
            $bids_data->current_bidder = $next_bidder;
        }

        DB::table('rounds')->where('id', $round_id)->update([
            'bids_data' => json_encode($bids_data)
        ]);

        event(new \App\Events\TarnibBidEvent($request->player_index, $request->amount));

        return response()->json();
    }

    function setTarnib(Request $request){
        $round_id = DB::table('rooms')->where('room_id', $request->room_id)->first()->round_id;
        DB::table('rounds')->where('id', $round_id)->update([
            'tarnib' => $request->tarnib
        ]);

        event(new \App\Events\TarnibChooseTarnibEvent($request->tarnib));

        return response()->json(['success' => true]);
    }

    function generateRandomString($length = 6) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
