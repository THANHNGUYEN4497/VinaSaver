<?php

namespace App;

use App\ChatHistory;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = 'chats';
    protected $dateFormat = 'U';
    protected $fillable = [
        'connector_id', 'job_id',
    ];

    public static function add($connector_id, $job_id)
    {
        $result = null;
        $check_chat = Chat::check_chat($connector_id, $job_id);
        if (!empty($check_chat)) {
            $result = $check_chat;
        } else {
            $company_id = Job::getCompanyIdByJobId($job_id)->company_id;
            $result = DB::table('chats')->insertGetId(array(
                'connector_id' => $connector_id,
                'job_id' => $job_id,
                'company_id' => $company_id,
                'created_at' => now()->timestamp,
                'updated_at' => now()->timestamp
             ));
        }
        return $result;
    }
    
    public static function check_chat($connector_id, $job_id)
    {
        $result = Chat::where('connector_id', $connector_id)
                        ->where('job_id', $job_id)
                        ->first();
        if (!empty($result)) {
            return $result->id;
        } else {
            return null;
        }
    }

    public static function getListChatByConnectorId($page_limit, $page_number, $connector_id, $keyword)
    {
        $page_number = ($page_number - 1) * $page_limit;
        $result = Chat::select(
            'chats.id',
            'chats.connector_id',
            'chats.company_id',
            'companies.company_name',
            'chats.job_id',
            'connectors.username',
            'connectors.avatar',
            'jobs.title AS job_title',
            'work_connections.status'
        )
        ->leftjoin('connectors', 'connectors.id', '=', 'chats.connector_id')
        ->leftjoin('jobs', 'jobs.id', '=', 'chats.job_id')
        ->leftjoin('companies', 'companies.id', '=', 'chats.company_id')
        ->leftjoin('work_connections', function ($join) use ($connector_id) {
            $join->on('work_connections.job_id', '=', 'chats.job_id');
            $join->where('work_connections.connector_id', '=', $connector_id);
        })
        ->where('chats.connector_id', $connector_id);
        if ($keyword) {
            $result = $result->where('companies.company_name', 'like', '%' . $keyword . '%');
        }
        
        $data = array();
        $data['total_items'] = $result->count();
        $data['path_avatar'] = Chat::getBasePath() . "/connector" . "/";
        $data['path_company_logo'] = Chat::getBasePath() . "/company" . "/";

        $result = $result->orderBy('id', 'desc')
            ->offset($page_number)
            ->limit($page_limit)
            ->get();
        $data['data'] = $result;
        if ($data) {
            return $data;
        } else {
            return null;
        }
    }

    public static function detailForApplicant($id)
    {
        $query = Job::select(
            'jobs.id',
            'jobs.introduction_title',
            'jobs.store_name',
            'categories.category_name',
            'job_categories.job_category_name'
        );
        $result = $query
            ->leftjoin('categories', 'categories.id', '=', 'jobs.category_id')
            ->leftjoin('job_categories', 'job_categories.id', '=', 'jobs.job_category_id')
            ->where('jobs.id', $id)
            ->first();
        return $result;
    }

    public static function getListChatByCompanyId($company_id)
    {
        $sql = "select
                chats.id,
                chats.connector_id,
                chats.job_id,
                connectors.username,
                connectors.avatar,
                jobs.title AS job_title,
                chat_histories.id AS chat_history_id,
                chat_histories.message AS message_lastest_not_seen,
                chat_histories.is_new,
                chat_histories.type
            FROM
                chats
            LEFT JOIN connectors ON connectors.id = chats.connector_id
            LEFT JOIN jobs ON jobs.id = chats.job_id
            INNER JOIN chat_histories ON chat_histories.chat_id = chats.id
            WHERE
                chat_histories.is_new = 1 AND chat_histories.type = 1 AND chats.company_id = " . $company_id . " order by time, order_no asc"
                ;

        $query_data = DB::select($sql);
        return $query_data;
    }

    public static function getTotalNotSeen($company_id)
    {
        $sql_count = "select
                        COUNT(*) AS total_items
                    FROM
                        chats
                    INNER JOIN chat_histories ON chat_histories.chat_id = chats.id
                    WHERE
                        chat_histories.is_new = 1 AND chat_histories.type = 1 AND chats.company_id = " . $company_id;
        $total_items = DB::select($sql_count);
        return $total_items[0];
    }

    public static function getListChatIdByConnectorIdAndJobId($connector_id, $job_id)
    {
        $result = Chat::query()
                    ->where('connector_id', $connector_id)
                    ->where('job_id', $job_id)
                    ->value('id');
        return $result;
    }

    public static function deleteById($id)
    {
        $chat_history = ChatHistory::where('chat_id', '=', $id)->delete();
        $chat = Chat::destroy($id);
        if ($chat == 1) {
            return true;
        } else {
            return false;
        }
        return false;
    }

    public static function getDetailById($id)
    {
        $result = Chat::select(
            'chats.id',
            'chats.connector_id',
            'chats.job_id',
            'chats.company_id',
            'connectors.username',
            'connectors.avatar',
            'work_connections.note'
        )
        ->leftjoin('connectors', 'connectors.id', '=', 'chats.connector_id')
        ->leftjoin('companies', 'companies.id', '=', 'chats.company_id')
        ->leftjoin('work_connections', function ($join) {
            $join->on('work_connections.job_id', '=', 'chats.job_id');
            $join->on('work_connections.connector_id', '=', 'chats.connector_id');
        })
        ->where('chats.id', $id)
        ->first();
        return $result;
    }

    public static function getChatInforExtendById($chat_id)
    {
        $result = Chat::select(
            'chats.id',
            'chats.connector_id',
            'chats.company_id',
            'companies.company_name',
            'companies.address as company_address',
            'companies.phone_number as company_phone_number',
            'chats.job_id',
            'connectors.username',
            'connectors.avatar'
        )
        ->leftjoin('connectors', 'connectors.id', '=', 'chats.connector_id')
        ->leftjoin('companies', 'companies.id', '=', 'chats.company_id')
        ->where('chats.id', $chat_id)
        ->first();
        return $result;
    }

    public static function getBasePath()
    {
        $path = '';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $path = 'https://' . $_SERVER['HTTP_HOST'] . '/upload';
        } else {
            $path = 'http://' . $_SERVER['HTTP_HOST'] . '/upload';
        }
        return $path;
    }
}
