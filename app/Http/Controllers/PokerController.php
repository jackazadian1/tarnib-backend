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
        $one_week_ago = date("Y-m-d H:i:s", strtotime("-1 week"));
        $three_days_ago = date("Y-m-d H:i:s", strtotime("-3 day"));
        DB::table('poker_rooms')->where('is_open', 1)->where('created_at', '<=', $three_days_ago)->update([
            'is_open' => 0
        ]);

        $rooms = DB::table('poker_rooms')->where('created_at', '<=', $one_week_ago);
        $rooms_data = $rooms->get();
        $room_ids = [];
        foreach($rooms_data as $room){
            $room_ids[] = $room->id;
        }

        DB::table('poker_players')->whereIn('id', $room_ids)->delete();
        $rooms->delete();
    }


    public function createRoom(Request $request){

        $room_id = $this->generateRandomString(); //todo make sure this is unique in db        

        $now = date('Y-m-d H:i:s');
        $id = DB::table('poker_rooms')->insertGetId([
            'room_id' => $room_id,
            'name' => $request->name,
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

        $room = DB::table('poker_rooms')->where('room_id', $request->room_id);
        $room_data = $room->first();
        $now = date('Y-m-d H:i:s');

        $id = DB::table('poker_players')->insertGetId([
            'room_id' => $room_data->id,
            'name' => $request->player_name,
            'buy_in_amount' => $request->buy_in_amount,
            'cash_out_amount' => -1,
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $history = json_decode($room_data->history);
        $room_data = $room->first();
        $updated_history = [
            "action" => "player_added",
            "player_id" => $id,
            "player_name" => $request->player_name,
            "buy_in_amount" => $request->buy_in_amount,
            "total_buy_in_amount" => $request->buy_in_amount,
            "time" => $request->time
        ];
        $history[] = $updated_history;
        $room->update([
            'history' => json_encode($history)
        ]);


        return response()->json(['success' => true, 'id' => $id, 'history' => $history]);
    }

    public function deletePokerPlayer(Request $request){
        $player = DB::table('poker_players')->where('id', $request->id);
        $player_data = $player->first();
        $room = DB::table('poker_rooms')->where('room_id', $request->room_id);
        $room_data = $room->first();

        $history = json_decode($room_data->history);
        $updated_history = [
            "action" => "player_deleted",
            "player_id" => $request->id,
            "player_name" => $player_data->name,
            "time" => $request->time
        ];
        $history[] = $updated_history;
        $room->update([
            'history' => json_encode($history)
        ]);

        $player->delete();
        return response()->json(['success' => true, 'history' => $history]);
    }

    public function addChips(Request $request){

        $player = DB::table('poker_players')->where('id', $request->id);
        $player_data = $player->first();
        $room = DB::table('poker_rooms')->where('room_id', $request->room_id);
        $room_data = $room->first();

        $current_balance = $player_data->buy_in_amount;
        $player->update([
            "buy_in_amount" => $current_balance + $request->amount
        ]);

        $history = json_decode($room_data->history);
        $updated_history = [
            "action" => "added_chips",
            "player_id" => $request->id,
            "player_name" => $player_data->name,
            "buy_in_amount" => $request->amount,
            "total_buy_in_amount" => $current_balance + $request->amount,
            "time" => $request->time
        ];
        $history[] = $updated_history;
        $room->update([
            'history' => json_encode($history)
        ]);

        return response()->json(['success' => true, 'new_balance' => $current_balance + $request->amount, 'history' => $history]);
    }

    public function cashout(Request $request){

        $player = DB::table('poker_players')->where('id', $request->id);
        $player_data = $player->first();
        $room = DB::table('poker_rooms')->where('room_id', $request->room_id);
        $room_data = $room->first();

        $player->update([
            "cash_out_amount" => $request->amount
        ]);

        $history = json_decode($room_data->history);
        $action = "player_cashed_out";
        if($request->amount == -1)
            $action = "undo_player_cashout";
        $updated_history = [
            "action" => $action,
            "player_id" => $request->id,
            "player_name" => $player_data->name,
            "cash_out_amount" => $request->amount,
            "time" => $request->time
        ];
        $history[] = $updated_history;

        $activePlayers = DB::table('poker_players')->where('room_id', $room_data->id)->where('cash_out_amount', -1)->get();

        $game_ended = false;
        $remaining_bank = 0;
        $last_player = -1;
        
        if(count($activePlayers) == 1 && $request->amount != -1){//end the game
            $last_player = $activePlayers[0]->id;
            $game_ended = true;
            $players = DB::table('poker_players')->where('room_id', $room_data->id)->get();

            foreach ($players as $key => $player) {
                $remaining_bank += $player->buy_in_amount;
                if($player->cash_out_amount != -1){
                    $remaining_bank -= $player->cash_out_amount;
                }
            }

            $last_player_db = DB::table('poker_players')->where('id', $last_player);
            $last_player_db->update([
                'cash_out_amount' => $remaining_bank
            ]);

            $new_updated_history = [
                "action" => 'player_cashed_out_remainder',
                "player_id" => $last_player,
                "player_name" => $last_player_db->first()->name,
                "cash_out_amount" => $remaining_bank,
                "time" => $request->time
            ];
            $history[] = $new_updated_history;
        }


        $room->update([
            'history' => json_encode($history)
        ]);

        return response()->json(['success' => true, 'game_ended' => $game_ended, 'last_player' => $last_player, 'remaining_bank' => $remaining_bank, 'history' => $history]);
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
