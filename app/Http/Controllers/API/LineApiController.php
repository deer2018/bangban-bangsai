<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Mylog;

class LineApiController extends Controller
{
    public function store(Request $request)
    {
        return "OK";
        //SAVE LOG

        // $requestData = $request->all();
        // $data = [
        //     "title" => "Line",
        //     "content" => json_encode($requestData, JSON_UNESCAPED_UNICODE),
        // ];
        // MyLog::create($data);

        //GET ONLY FIRST EVENT
        // $event = $requestData["events"][0];

        // switch($event["type"]){
        //     case "message" :
        //         $this->messageHandler($event);
        //         break;
        //     case "postback" :
        //         $this->postbackHandler($event);
        //         break;
        //     case "join" :
        //         $this->save_group_line($event);
        //         break;
        //     case "follow" :
        //         $this->user_follow_line($event);
        //         DB::table('users')
        //             ->where([
        //                     ['type', 'line'],
        //                     ['provider_id', $event['source']['userId']],
        //                     ['status', "active"]
        //                 ])
        //             ->update(['add_line' => 'Yes']);
        //         break;
        // }
    }
}
