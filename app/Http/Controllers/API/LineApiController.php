<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Mylog;
use Intervention\Image\ImageManager;
use Intervention\Image\ImageManagerStatic as Image;

class LineApiController extends Controller
{
    public function store(Request $request)
    {
        //SAVE LOG
        $requestData = $request->all();
        $data = [
            "title" => "Line",
            "content" => json_encode($requestData, JSON_UNESCAPED_UNICODE),
        ];
        MyLog::create($data);

        //GET ONLY FIRST EVENT
        $event = $requestData["events"][0];

        switch($event["type"]){
            case "message" :
                if($event["message"]["type"] == "image"){
                    $this->image_convert($event);
                }else{
                    $this->messageHandler($event);
                }
                break;
            case "postback" :
                // $this->postbackHandler($event);
                break;
            case "join" :
                // $this->save_group_line($event);
                break;
            // case "follow" :
            //     $this->user_follow_line($event);
            //     DB::table('users')
            //         ->where([
            //                 ['type', 'line'],
            //                 ['provider_id', $event['source']['userId']],
            //                 ['status', "active"]
            //             ])
            //         ->update(['add_line' => 'Yes']);
            //     break;
        }
    }

    public function image_convert($event)
    {
        //LOAD REMOTE IMAGE AND SAVE TO LOCAL
        $binary_data  = $this->getImageFromLine($event["message"]["id"]);
        $filename = $this->random_string(20).".png";
        $new_path = storage_path('app/public').'/uploads/ocr/'.$filename;

        Image::make($binary_data)->save($new_path);

        $image = Image::make( storage_path('app/public').'/uploads/ocr/'.$filename );
        $watermark = Image::make( public_path('img/logo/green-logo-01.png') );
        $image->insert($watermark ,'bottom-right', 385, 150);
        $image->save();

        $template_path = storage_path('../public/json/flex_img.json');
        $string_json = file_get_contents($template_path);

        $string_json = str_replace("ตัวอย่าง" , 'ส่งรูปภาพ' ,$string_json);
        $string_json = str_replace("FILENAME" , $filename ,$string_json);

        $messages = [ json_decode($string_json, true) ];

        $body = [
            "replyToken" => $event["replyToken"],
            "messages" => $messages,
        ];

        $opts = [
            'http' =>[
                'method'  => 'POST',
                'header'  => "Content-Type: application/json \r\n".
                            'Authorization: Bearer '.env('CHANNEL_ACCESS_TOKEN'),
                'content' => json_encode($body, JSON_UNESCAPED_UNICODE),
                //'timeout' => 60
            ]
        ];

        $context  = stream_context_create($opts);
        //https://api-data.line.me/v2/bot/message/11914912908139/content
        $url = "https://api.line.me/v2/bot/message/reply";
        $result = file_get_contents($url, false, $context);

        //SAVE LOG
        $data = [
            "title" => "ตอบกลับผู้ใช้",
            "content" => "สวัสดีค่ะ ได้รับรูปภาพแล้วค่ะ",
        ];
        MyLog::create($data);
        return $result;

    }

    public function getImageFromLine($id){
        $opts = array('http' =>[
                'method'  => 'GET',
                //'header'  => "Content-Type: text/xml\r\n".
                'header' => 'Authorization: Bearer '.env('CHANNEL_ACCESS_TOKEN'),
                //'content' => $body,
                //'timeout' => 60
            ]
        );

        $context  = stream_context_create($opts);
        //https://api-data.line.me/v2/bot/message/11914912908139/content
        $url = "https://api-data.line.me/v2/bot/message/{$id}/content";
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    public function random_string($length) {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }

        return $key;
    }

    public function messageHandler($event)
    {
        switch($event["message"]["type"]){
            case "text" : 
                $this->textHandler($event);
                break;
        }   

    }

    public function textHandler($event)
    {
            
        switch( strtolower($event["message"]["text"]) ){     
            case "ระดับน้ำ" :  
                $this->replyToUser(null, $event, "water level");
                break;
            default :
                $this->replyToUser(null, $event, $event["message"]["text"]);
                break;
        } 
    }

    public function replyToUser($data, $event, $message_type)
    {
        switch($message_type)
        {
            case 'water level':
                $template_path = storage_path('../public/json/quick_reply.json');   
                $string_json = file_get_contents($template_path);

                $messages = [ json_decode($string_json, true) ]; 
            break;
            case $message_type:
                $template_path = storage_path('../public/json/text.json');   
                $string_json = file_get_contents($template_path);

                $string_json = str_replace("ตัวอย่าง" , $message_type ,$string_json);
                $string_json = str_replace("เปลี่ยนข้อความตรงนี้" , $message_type ,$string_json);

                $messages = [ json_decode($string_json, true) ]; 
            break;
        }

        $body = [
            "replyToken" => $event["replyToken"],
            "messages" => $messages,
        ];

        $opts = [
            'http' =>[
                'method'  => 'POST',
                'header'  => "Content-Type: application/json \r\n".
                            'Authorization: Bearer '.env('CHANNEL_ACCESS_TOKEN'),
                'content' => json_encode($body, JSON_UNESCAPED_UNICODE),
                //'timeout' => 60
            ]
        ];
                            
        $context  = stream_context_create($opts);
        //https://api-data.line.me/v2/bot/message/11914912908139/content
        $url = "https://api.line.me/v2/bot/message/reply";
        $result = file_get_contents($url, false, $context);

        //SAVE LOG
        $data = [
            "title" => "reply Success",
            "content" => "message quickReply",
        ];
        MyLog::create($data);

        return $result;

    }

}
