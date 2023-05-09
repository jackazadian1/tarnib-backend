<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

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

    public function createRoom(Request $request){

        $room_id = $this->generateRandomString(); //todo make sure this is unique in db        

        $id = DB::table('rooms')->insertGetId([
            'room_id' => $room_id,
            'player_1' => $request->player_name,
            'player_1_token' => $request->player_token,
            'team_1_score' => 0,
            'team_2_score' => 0,
        ]);

        DB::table('rooms')->where('id', $id)->update([
            'round_id' => $this->createRound($id)
        ]);


        return response()->json(['success' => true, 'room_id' => $room_id, 'player_name' => $request->player_name]);

    }

    function createRound($room_id){

        $random_cards = $this->cards;
        shuffle($random_cards);
        $round_number = count(DB::table('rounds')->where('room_id', $room_id)->get());
        $dealer= $round_number == 0 ? rand(0,3) : $round_number%4;
        $round_id = DB::table('rounds')->insertGetId([
            'room_id' => $room_id,
            'player_1_cards' => json_encode($this->sortCards(array_slice($random_cards, 0, 13))),
            'player_2_cards' => json_encode($this->sortCards(array_slice($random_cards, 13, 13))),
            'player_3_cards' => json_encode($this->sortCards(array_slice($random_cards, 26, 13))),
            'player_4_cards' => json_encode($this->sortCards(array_slice($random_cards, 39, 13))),
            'turn' => 1,
            'team_1_score' => 0,
            'team_2_score' => 0,
            'dealer' => $dealer,
            'bids_data' => json_encode([
                'bids' => [0,0,0,0],
                'current_bidder' => ($round_number+1)%4
            ]),
            'current_play' => json_encode(['','','','']),
            'player_turn' => -1,

        ]);

        return $round_id;
    }

    function joinRoom(Request $request){
        $room = DB::table('rooms')->where('room_id', $request->room_id)->first();
        $player_seat = '';
        if($room->player_1 == $request->player_name) $player_seat = 1;
        else if($room->player_2 == $request->player_name) $player_seat = 2;
        else if($room->player_3 == $request->player_name) $player_seat = 3;
        else if($room->player_4 == $request->player_name) $player_seat = 4;


        $round_number = count(DB::table('rounds')->where('room_id', $room_id)->get());

        //$round_count = count(DB::table('rounds')->where('id', $room->round_id)->get());

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

        // if($cards != []){
        //     $cards = $this->sortCards($cards);
        // }

        

        return view('game')->with([
            'player_name' => $request->player_name,
            'player_seat' => $player_seat,
            'room_id' => $room->room_id,
            'player_1' => $room->player_1,
            'player_2' => $room->player_2,
            'player_3' => $room->player_3,
            'player_4' => $room->player_4,
            'round' => $round_number,
            'turn' => $round->turn,
            'cards' => $cards,
            'bids_data' => json_decode($round->bids_data),
            'goal' => $round->goal,
            'player_turn' => $round->player_turn,
            'current_play' => json_decode($round->current_play)
        ]);
    }

    function getRoundInfo(Request $request){
        $room = DB::table('rooms')->where('room_id', $request->room_id)->first();
        $round = DB::table('rounds')->where('id', $room->round_id)->first();
        $round_number = count(DB::table('rounds')->where('room_id', $room->id)->get());
        //$round_count = count(DB::table('rounds')->where('id', $room->round_id)->get());

        $player_seat = '';
        if($room->player_1_token == $request->player_token) $player_seat = 1;
        else if($room->player_2_token == $request->player_token) $player_seat = 2;
        else if($room->player_3_token == $request->player_token) $player_seat = 3;
        else if($room->player_4_token == $request->player_token) $player_seat = 4;

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
        $player_name_number = "player_".$player_seat;
        return response()->json([
            'player_name' => $player_seat == '' ? null : $room->$player_name_number,
            'player_seat' => $player_seat,
            'room_id' => $room->room_id,
            'player_1' => $room->player_1,
            'player_2' => $room->player_2,
            'player_3' => $room->player_3,
            'player_4' => $room->player_4,
            'round' => $round_number,
            'turn' => $round->turn,
            'cards' => $cards,
            'dealer' => $round->dealer,
            'tarnib' => $round->tarnib,
            'bids_data' => json_decode($round->bids_data),
            'goal' => $round->goal,
            'player_turn' => $round->player_turn,
            'current_play' => json_decode($round->current_play),
            'team_1_score' => $round->team_1_score,
            'team_2_score' => $round->team_2_score,
            'team_1_game_score' => $room->team_1_score,
            'team_2_game_score' => $room->team_2_score,
        ]);
    }

    function chooseSeat(Request $request){
        $player_number = "player_".$request->seat;
        $player_number_token = "player_".$request->seat."_token";
        $room = DB::table('rooms')->where('room_id', $request->room_id);
        $room->update([
            $player_number => $request->player_name,
            $player_number_token => $request->player_token
        ]);

        $round = DB::table('rounds')->where('id', $room->first()->round_id)->first();
        $player_cards = 'player_'.$request->seat.'_cards';
        
        broadcast(new \App\Events\TarnibChooseSeatEvent($request->seat, $request->player_name, $request->room_id))->toOthers();

        return response()->json([
            'player_cards' => json_decode($round->$player_cards)
        ]);
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
                if ($val2 > $val1) {
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

        broadcast(new \App\Events\TarnibBidEvent($bids_data, $request->room_id))->toOthers();

        return response()->json($bids_data);
    }

    function setTarnib(Request $request){
        $round_id = DB::table('rooms')->where('room_id', $request->room_id)->first()->round_id;
        $data = [
            'tarnib' => $request->tarnib,
            'goal' => $request->goal,
            'player_turn' => $request->player_turn
        ];
        DB::table('rounds')->where('id', $round_id)->update($data);

        broadcast(new \App\Events\TarnibChooseTarnibEvent($data, $request->room_id))->toOthers();

        return response()->json($data);
    }

    function playCard(Request $request){
        $round_id = DB::table('rooms')->where('room_id', $request->room_id)->first()->round_id;

        $round = DB::table('rounds')->where('id', $round_id);

        $current_play = json_decode($round->first()->current_play);

        $current_play[$request->player_seat-1] = $request->card;
        $player = 'player_'.$request->player_seat.'_cards';
        $player_cards = json_decode($round->first()->$player);
        unset($player_cards[array_search($request->card, $player_cards)]);

        $res = [
            'current_play' => $current_play,
            'player_turn' => $round->first()->player_turn + 1 > 3 ? 0 : $round->first()->player_turn + 1,
            'player_cards' => array_values($player_cards)
        ];
        $round->update([
            'current_play' => json_encode($current_play),
            'player_turn' => $round->first()->player_turn + 1 > 3 ? 0 : $round->first()->player_turn + 1,
            'player_'.$request->player_seat.'_cards' => array_values($player_cards)
        ]);

        broadcast(new \App\Events\TarnibPlayCardEvent($res, $request->room_id))->toOthers();

        return response()->json($res);
    }

    function setNewTurn(Request $request){
        $round_id = DB::table('rooms')->where('room_id', $request->room_id)->first()->round_id;

        $round = DB::table('rounds')->where('id', $round_id);
        $round_data = $round->first();

        $highest_card_index = $this->findHighestCard(json_decode($round_data->current_play), $this->cardValue(json_decode($round_data->current_play)[intval($round_data->player_turn)])['suit'], $round_data->tarnib);
        $scoring_team = '';

        if($highest_card_index == 0 || $highest_card_index == 2){
            $scoring_team = 'team_1_score';
        }else{
            $scoring_team = 'team_2_score';
        }
        $score = intval($round_data->$scoring_team)+1;
        $turn = intval($round_data->turn)+1;

        $round->update([
            'turn' => $turn,
            $scoring_team => $score,
            'player_turn' => $highest_card_index,
            'current_play' => ['','','','']
        ]);

        $res = [
            'turn' => $turn,
            $scoring_team => $score,
            'player_turn' => $highest_card_index,
            'previous_play' =>$request->previous_play,
        ];

        broadcast(new \App\Events\TarnibNewTurnEvent($res, $request->room_id))->toOthers();

        return response()->json($res);

    }

    public function test(){
        $highest_card_index = $this->findHighestCard(["10_of_diamonds", "12_of_diamonds", "12_of_hearts", "8_of_clubs"], 'clubs', 'spades');

        return response()->json($highest_card_index);
    }

    function findHighestCard($cards, $current_play_suit, $tarnib){
        $highest_index = 0;

        for ($i=1; $i < count($cards); $i++) { 
            clock($cards[$i]);
            clock($highest_index);
            clock($this->cardValue($cards[$i])['suit'] == $current_play_suit);
            clock($this->cardValue($cards[$highest_index])['suit'] == $tarnib);

            if($this->cardValue($cards[$i])['suit'] == $current_play_suit){
                if($this->cardValue($cards[$highest_index])['suit'] == $tarnib){
                    if($current_play_suit ==  $tarnib){
                        if($this->cardValue($cards[$i])['value'] > $this->cardValue($cards[$highest_index])['value']){
                            $highest_index = $i;
                        }
                    }
                }else{
                    if($current_play_suit !=  $tarnib){
                        if($this->cardValue($cards[$highest_index])['suit'] != $current_play_suit){
                            $highest_index = $i;
                        }else{
                            if($this->cardValue($cards[$i])['value'] > $this->cardValue($cards[$highest_index])['value']){
                                $highest_index = $i;
                            }
                        }
                        
                    }else{
                        $highest_index = $i;
                    }
                }
            }else{
                if($this->cardValue($cards[$i])['suit'] == $tarnib){
                    if($this->cardValue($cards[$highest_index])['suit'] == $tarnib){
                        if($this->cardValue($cards[$i])['value'] > $this->cardValue($cards[$highest_index])['value']){
                            $highest_index = $i;
                        }
                    }else{
                        $highest_index = $i;
                    }
                }
            }
        }

        return $highest_index;
    }

    function cardValue($card){
        return [
            "value" => intval(explode('_of_',$card)[0]),
            "suit" => explode('_of_',$card)[1]
        ];
    }

    function setNewRound(Request $request){

        $room = DB::table('rooms')->where('room_id', $request->room_id);
        $old_round = DB::table('rounds')->where('id',$room->first()->round_id)->first();


        $bid_winner = json_decode($old_round->bids_data)->current_bidder;

        $update = [
            'team_1_score' => $room->first()->team_1_score,
            'team_2_score' => $room->first()->team_2_score,
        ];

        if($bid_winner == 0 || $bid_winner == 2){
            if($old_round->goal <= $old_round->team_1_score){
                $update['team_1_score'] = $update['team_1_score'] + $old_round->team_1_score;
            }else{
                $update['team_1_score'] = $update['team_1_score'] - $old_round->goal;
                $update['team_2_score'] = $update['team_2_score'] + $old_round->team_2_score;
            }
        }else{
            if($old_round->goal <= $old_round->team_2_score){
                $update['team_2_score'] = $update['team_2_score'] + $old_round->team_2_score;
            }else{
                $update['team_2_score'] = $update['team_2_score'] - $old_round->goal;
                $update['team_1_score'] = $update['team_1_score'] + $old_round->team_1_score;
            }
        }
        $new_round_id = $this->createRound($room->first()->id);

        $new_round = DB::table('rounds')->where('id', $new_round_id)->first();

        $update['round_id'] = $new_round_id;

        $room->update($update);

        $player_cards = 'player_'.$request->player_seat.'_cards';
        $round_number = count(DB::table('rounds')->where('room_id', $room->first()->id)->get());

        for ($i=1; $i <= 4; $i++) { 
            $player_token_number = 'player_'.$i.'_token';
            $player_token = $room->first()->$player_token_number;
            if($player_token != $request->player_token){
                $player_cards_number = 'player_'.$i.'_cards';
                $data = [
                    'round' => $round_number,
                    'turn' => $new_round->turn,
                    'cards' => json_decode($new_round->$player_cards_number),
                    'dealer' => $new_round->dealer,
                    'tarnib' => $new_round->tarnib,
                    'bids_data' => json_decode($new_round->bids_data),
                    'goal' => $new_round->goal,
                    'player_turn' => $new_round->player_turn,
                    'current_play' => json_decode($new_round->current_play),
                    'team_1_score' => $new_round->team_1_score,
                    'team_2_score' => $new_round->team_2_score,
                    'team_1_game_score' => $room->first()->team_1_score,
                    'team_2_game_score' => $room->first()->team_2_score,
                ]; 
                broadcast(new \App\Events\TarnibNewRoundEvent($data, $request->room_id, $player_token));
            }
        }

        


        return response()->json($res = [
            'round' => $round_number,
            'turn' => $new_round->turn,
            'cards' => json_decode($new_round->$player_cards),
            'dealer' => $new_round->dealer,
            'tarnib' => $new_round->tarnib,
            'bids_data' => json_decode($new_round->bids_data),
            'goal' => $new_round->goal,
            'player_turn' => $new_round->player_turn,
            'current_play' => json_decode($new_round->current_play),
            'team_1_score' => $new_round->team_1_score,
            'team_2_score' => $new_round->team_2_score,
            'team_1_game_score' => $room->first()->team_1_score,
            'team_2_game_score' => $room->first()->team_2_score,
        ]);

    }

    function moveToNewRoom(Request $request){
        $room = DB::table('rooms')->where('room_id', $request->room_id)->first();

        $new_room_id = $this->generateRandomString(); 

        $id = DB::table('rooms')->insertGetId([
            'room_id' => $new_room_id,
            'player_1' => $room->player_1,
            'player_1_token' => $room->player_1_token,
            'player_2' => $room->player_2,
            'player_2_token' => $room->player_2_token,
            'player_3' => $room->player_3,
            'player_3_token' => $room->player_3_token,
            'player_4' => $room->player_4,
            'player_4_token' => $room->player_4_token,
            'team_1_score' => 0,
            'team_2_score' => 0,
        ]);

        DB::table('rooms')->where('id', $id)->update([
            'round_id' => $this->createRound($id)
        ]);

        broadcast(new \App\Events\TarnibMoveToNewRoomEvent($new_room_id, $request->room_id))->toOthers();

        return response()->json(['room_id' => $new_room_id]);
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
