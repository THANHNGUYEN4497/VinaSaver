<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ChatHistory extends Model
{
    protected $table = 'chat_histories';
    protected $dateFormat = 'U';
    public $fillable = [
        'chat_id','type', 'message','time','is_new','order_no'
    ];
    
    public static function add($chat_id, $type, $message, $time, $order_no)
    {
        $id = DB::table('chat_histories')->insertGetId(array(
            'chat_id' => $chat_id,
            'type' => $type,
            'message' => $message,
            'time' => $time,
            'order_no' => $order_no,
            'created_at' => now()->timestamp,
            'updated_at' => now()->timestamp
        ));
        return $id;
    }

    public static function getListChatDetailByChatId($chat_id)
    {
        $result = ChatHistory::query()
                    ->where('chat_id', $chat_id)
                    ->orderBy('time', 'asc')
                    ->orderBy('order_no', 'asc')
                    ->get();
        return $result;
    }

    public static function getListChatDetailByChatIdForApp($page_limit, $page_number, $chat_id)
    {
        $page_number = ($page_number - 1) * $page_limit;
        $result = ChatHistory::select('id', 'chat_id', 'message', 'time', 'type')
                    ->where('chat_id', $chat_id);

        $data = array();
        $data['total_items'] = $result->count();

        $result = $result->orderBy('time', 'desc')
                    ->orderBy('order_no', 'desc')
                    ->offset($page_number)
            ->limit($page_limit)
            ->get();
        $data['data'] = $result;

        if ($data) {
            return $data;
        } else {
            return null;
        }
        return $result;
    }

    public static function updateMessageStatus($chat_id)
    {
        ChatHistory::where('chat_id', $chat_id)->where('type', 1)->update(['is_new' => 0]);
    }
}
