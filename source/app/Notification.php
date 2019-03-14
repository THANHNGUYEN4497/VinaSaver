<?php

namespace App;

use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $dateFormat = 'U';
    protected $fillable = [
        'connector_id', 'content', 'type', 'work_connection_id', 'company_id'
        //type: 1 is accept, 2 is ignore, 3 is recruit, 4 is bonus, 5 is delete, 6 is report
    ];

    //
    public static function add($connector_id, $content, $type, $work_connection_id, $company_id)
    {
        $notification = new Notification();
        $notification->connector_id = $connector_id;
        $notification->content = $content;
        $notification->type = $type;
        $notification->work_connection_id = $work_connection_id;
        $notification->company_id = $company_id;
        $notification->save();
        return $notification->id;
    }

    public static function getByConnector($connector_id, $page_number, $page_limit)
    {
        $page_number = ($page_number - 1) * $page_limit;
        $query = Notification::select(
            'notifications.id',
            'notifications.connector_id',
            'notifications.content',
            'notifications.type',
            'notifications.work_connection_id',
            'notifications.company_id',
            'companies.company_name',
            'notifications.created_at'
        )
        ->leftjoin('companies', 'companies.id', '=', 'notifications.company_id')
        ->where('notifications.connector_id', $connector_id);
        $data = array();
        $data['total_items'] = $query->count();

        $query = $query->orderBy('notifications.id', 'desc')
            ->offset($page_number)
            ->limit($page_limit)
            ->get();
        if (count($query) > 0) {
            for ($i = 0; $i < count($query); $i++) {
                $query[$i]['image_company'] = CompanyFile::getMainImage($query[$i]['company_id']);
                $query[$i]['base_path_company'] = Company::getBasePath();
            }
        }
        $data['data'] = $query;
        return $data;
    }
}
