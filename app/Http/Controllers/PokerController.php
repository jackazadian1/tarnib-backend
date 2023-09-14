<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

class PokerController extends Controller
{
    public function __construct()
    {
        $one_week_ago = date("Y-m-d H:i:s", strtotime("-3 day"));
        DB::table('poker_rooms')->where('created_at', '<=', $one_week_ago)->update([
            'is_open' => 0
        ]);
    }


    public function createRoom(Request $request){

        $room_id = $this->generateRandomString(); //todo make sure this is unique in db        

        $now = date('Y-m-d H:i:s');
        $id = DB::table('poker_rooms')->insertGetId([
            'room_id' => $room_id,
            'password' => $request->password,
            'is_open' => true,
            'created_at' => $now,
            'updated_at' => $now
        ]);


        return response()->json(['success' => true, 'room_id' => $room_id]);
    }

    function getData(Request $request){
        $room = DB::table('poker_rooms')->where('room_id', $request->room_id)->first();
        $players = DB::table('poker_players')->where('room_id', $room->id)->get();
        return response()->json(['room' => $room, 'players' => $players]);
    }

    function getRooms(){
        $rooms = DB::table('poker_rooms')->get();

        foreach($rooms as $room){
            $room->password = $room->password != null;
        }
        return response()->json(['rooms' => $rooms]);
    }

    function passwordCheck(Request $request){
        $room = DB::table('poker_rooms')->where('room_id', $request->room_id)->first();

        return response()->json(['date' => $room->created_at, 'has_password' => $room->password != null]);
    }

    function authenticate(Request $request){
        $room = DB::table('poker_rooms')->where('room_id', $request->room_id)->first();

        return response()->json($room->password == $request->password);
    }

    public function addPokerPlayer(Request $request){

        $room = DB::table('poker_rooms')->where('room_id', $request->room_id)->first();
        $now = date('Y-m-d H:i:s');

        $id = DB::table('poker_players')->insertGetId([
            'room_id' => $room->id,
            'name' => $request->player_name,
            'buy_in_amount' => $request->buy_in_amount,
            'cash_out_amount' => -1,
            'created_at' => $now,
            'updated_at' => $now
        ]);


        return response()->json(['success' => true, 'id' => $id]);
    }

    public function deletePokerPlayer(Request $request){
        DB::table('poker_players')->where('id', $request->id)->delete();
        return response()->json(['success' => true]);
    }

    public function addChips(Request $request){

        $player = DB::table('poker_players')->where('id', $request->id);
        $current_balance = $player->first()->buy_in_amount;
        $player->update([
            "buy_in_amount" => $current_balance + $request->amount
        ]);

        return response()->json(['success' => true, 'new_balance' => $current_balance + $request->amount]);
    }

    public function cashout(Request $request){

        $player = DB::table('poker_players')->where('id', $request->id)->update([
            "cash_out_amount" => $request->amount
        ]);

        $room = DB::table('poker_rooms')->where('room_id', $request->room_id);

        $activePlayers = DB::table('poker_players')->where('room_id', $room->first()->id)->where('cash_out_amount', -1)->get();

        $game_ended = false;
        $remaining_bank = 0;
        $last_player = -1;
        if(count($activePlayers) == 1 && $request->amount != -1){//end the game
            $last_player = $activePlayers[0]->id;
            $game_ended = true;
            $players = DB::table('poker_players')->where('room_id', $room->first()->id)->get();

            foreach ($players as $key => $player) {
                $remaining_bank += $player->buy_in_amount;
                if($player->cash_out_amount != -1){
                    $remaining_bank -= $player->cash_out_amount;
                }
            }

            DB::table('poker_players')->where('id', $last_player)->update([
                'cash_out_amount' => $remaining_bank
            ]);
        }

        return response()->json(['success' => true, 'game_ended' => $game_ended, 'last_player' => $last_player, 'remaining_bank' => $remaining_bank]);
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
