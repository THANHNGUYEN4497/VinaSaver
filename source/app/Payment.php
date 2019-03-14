<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Payment extends Model
{
    protected $table = 'payments';
    protected $dateFormat = 'U';
    //status = 0: request payment, status = 1: paid
    //type=1: CONET -> connector, type = 2 : company -> CONET, type = 3 : company -> connector
    protected $fillable = [
        'connector_id', 'company_id', 'job_id', 'type', 'content', 'amount', 'status'
    ];
    
    public static function add($connector_id, $company_id, $job_id, $type, $content, $amount, $status)
    {
        $result = DB::table('payments')->insertGetId(array(
            'connector_id' => $connector_id,
            'company_id' => $company_id,
            'job_id' => $job_id,
            'amount' => $amount,
            'content' => $content,
            'type' => $type,
            'status' => 0,
            'created_at' => now()->timestamp,
            'updated_at' => now()->timestamp
            ));
        return $result;
    }

    public static function updateStatus($payment_id, $status)
    {
        $payment = Payment::find($payment_id);
        if (!empty($status)) {
            $payment->status = $status;
        }
        $payment->save();
        return ($payment) ? true : false;
    }

    public static function getListRequestByConnectorId($connector_id, $page_number, $page_limit)
    {
        $result = Payment::select('id', 'connector_id', 'company_id', 'job_id', 'content', 'amount', 'status', 'created_at', 'updated_at')
                    ->where('connector_id', $connector_id)
                    ->where('type', 1);
        $count = $result->count();
        $result->offset(($page_number - 1) * $page_limit)
            ->limit($page_limit);
        return array('total_item' => $count, 'data' => $result->get());
    }
    
    public static function getListPaymentByConnectorId($connector_id, $status, $page_number, $page_limit)
    {
        $result = Payment::select('id', 'connector_id', 'company_id', 'job_id', 'content', 'amount', 'created_at', 'updated_at')
                    ->where('connector_id', $connector_id)
                    ->whereIn('type', [1,3])
                    ->where('status', $status);
        $count = $result->count();
        $result->offset(($page_number - 1) * $page_limit)->limit($page_limit);
        return array('total_item' => $count, 'data' => $result->get());
    }

    public static function listPaymentByCompany($company_id, $keyword, $date_create_start, $date_create_end, $page_number, $page_limit)
    {
        $result = Payment::select('payments.id', 'payments.status', 'payments.amount', 'payments.content', 'connectors.username as connector_username', 'payments.created_at')
                ->leftjoin('connectors', 'connectors.id', '=', 'payments.connector_id')
                ->leftjoin('companies', 'companies.id', '=', 'payments.company_id');
        if (!empty($keyword)) {
            $result = $result ->where(function ($query) use ($keyword) {
                if (!empty($keyword)) {
                    $query->where('connectors.username', 'like', "%$keyword%");
                    $query->orwhere('payments.content', 'like', "%$keyword%");
                }
            });
        }
        if ($date_create_start) {
            $date_create_start = Carbon::parse("$date_create_start 00:00:00")->timestamp;
            $result = $result->where('payments.created_at', '>=', $date_create_start);
        }
        if ($date_create_end) {
            $date_create_end = Carbon::parse("$date_create_end 23:59:59")->timestamp;
            $result = $result->where('payments.created_at', '<=', $date_create_end);
        }
        $result->whereIn('payments.type', [2,3]);
        $result->where('payments.company_id', $company_id);

        $count = $result->count();
        $result->offset(($page_number - 1) * $page_limit)
            ->limit($page_limit);
        return array('total_item' => $count, 'data' => $result->get());
    }

    public static function listPaymentByConet($type, $keyword, $date_create_start, $date_create_end, $page_number, $page_limit)
    {
        $result = Payment::select('payments.id', 'payments.status', 'payments.amount', 'payments.content', 'connectors.username as connector_username', 'companies.company_name', 'payments.created_at')
                ->leftjoin('connectors', 'connectors.id', '=', 'payments.connector_id')
                ->leftjoin('companies', 'companies.id', '=', 'payments.company_id');
        if (!empty($keyword)) {
            $result->where('connectors.username', 'like', "%$keyword%");
            $result->orwhere('payments.content', 'like', "%$keyword%");
        }
        if ($date_create_start) {
            $date_create_start = Carbon::parse("$date_create_start 00:00:00")->timestamp;
            $result->where('payments.created_at', '>=', $date_create_start);
        }
        if ($date_create_end) {
            $date_create_end = Carbon::parse("$date_create_end 23:59:59")->timestamp;
            $result->where('payments.created_at', '<=', $date_create_end);
        }
        $result->where('payments.type', $type);

        $count = $result->count();
        $result->offset(($page_number - 1) * $page_limit)
            ->limit($page_limit);
        return array('total_item' => $count, 'data' => $result->get());
    }
}
