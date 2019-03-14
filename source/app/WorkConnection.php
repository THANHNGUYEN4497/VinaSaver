<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Job;

class WorkConnection extends Model
{
    protected $table = 'work_connections';
    protected $dateFormat = 'U';
    //status =0: apply a job;status =1: accept CV;  status=2: is recruited; status=3: is report
    //report_by_connector,report_by_company 「良い」:1、「普通」:2、「良くない」:3
    protected $fillable = [
        'connector_id', 'introduction_id', 'job_id','status','recruited_time_by_connector','recruited_time_by_company',
        'report_by_connector','report_by_company','recruitment_reason', 'is_new'
    ];
    
    public static function add($job_id, $connector_id, $introduction_id)
    {
        $result = DB::table('work_connections')->insertGetId(array(
            'connector_id' => $connector_id,
            'introduction_id' => !empty($introduction_id) ? $introduction_id : null,
            'job_id' => $job_id,
            'status' => 0,
            'created_at' => now()->timestamp,
            'updated_at' => now()->timestamp
        ));
        return $result;
    }

    public static function edit($id, $status, $recruited_time_by_connector, $recruited_time_by_company, $report_by_connector, $report_by_company, $note, $recruitment_reason)
    {
        $info = WorkConnection::find($id);
        if ($info) {
            if (!is_null($status)) {
                $info->status = $status;
            }
            if (!empty($recruited_time_by_connector)) {
                $info->recruited_time_by_connector = $recruited_time_by_connector;
            }
            if (!empty($recruited_time_by_company)) {
                $info->recruited_time_by_company = Carbon::parse("$recruited_time_by_company")->timestamp;
            }
            if (!is_null($report_by_connector)) {
                $info->report_by_connector = $report_by_connector;
            }
            if (!is_null($report_by_company)) {
                $info->report_by_company = $report_by_company;
            }
            if (!empty($recruitment_reason)) {
                $info->recruitment_reason = $recruitment_reason;
            }
            if (!empty($note)) {
                $info->note = $note;
            }
            $info->save();
            return $info->id;
        } else {
            return null;
        }
    }

    public static function infoToNotify($id)
    {
        $info = WorkConnection::select(
            'work_connections.id',
            'work_connections.job_id',
            'work_connections.connector_id',
            'jobs.title',
            'jobs.company_id',
            'companies.company_name'
        )
        ->join('jobs', 'jobs.id', '=', 'work_connections.job_id')
        ->leftjoin('companies', 'companies.id', '=', 'jobs.company_id')
        ->where('work_connections.id', $id)
        ->first();
        return $info;
    }

    public static function getListApplicantByJobId($job_id, $page_limit, $page_number, $keyword, $status)
    {
        $page_number = ($page_number - 1) * $page_limit;
        $result = WorkConnection::select(
            'work_connections.id',
            'work_connections.job_id',
            'work_connections.introduction_id',
            'work_connections.status',
            'work_connections.created_at as apply_date',
            'connectors.username',
            'connectors.username',
            'connectors.email',
            'connectors.phone_number',
            'connectors.birthday',
            'connectors.gender',
            'connectors.id as connector_id'
        )
                    ->leftjoin('connectors', 'connectors.id', '=', 'work_connections.connector_id')
                    ->where('work_connections.job_id', $job_id);
    
        $result = $result ->where(function ($query) use ($keyword, $status) {
            if (!empty($keyword)) {
                $query->where('connectors.username', 'LIKE', '%' . $keyword . '%');
                $query->orwhere('connectors.email', 'LIKE', '%' . $keyword . '%');
                $query->orwhere('connectors.phone_number', 'LIKE', '%' . $keyword . '%');
                if (!is_null($status)) {
                    $query->orwhere('work_connections.status', $status);
                }
            } else {
                if (!is_null($status)) {
                    $query->where('work_connections.status', $status);
                }
            }
        });

        $data['total_items']  = $result->count();
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

    //
    //  do not delete.
    //
    public static function deleteApplicant($id)
    {
        $work_connection = WorkConnection::destroy($id);
        if ($work_connection) {
            return true;
        } else {
            return false;
        }
    }

    public static function getStatusApplicant($id)
    {
        $work_connection = WorkConnection::find($id);
        if ($work_connection) {
            return $work_connection->status;
        }
        return null;
    }

    public static function getByStaff($job_id, $page_limit, $page_number, $keyword, $status)
    {
        $result = WorkConnection::select(
            'work_connections.id',
            'work_connections.job_id',
            'work_connections.connector_id',
            'work_connections.introduction_id',
            'work_connections.status',
            'work_connections.created_at as apply_date',
            'work_connections.is_new',
            'connectors.username',
            'connectors.email',
            'connectors.phone_number',
            'connectors.birthday',
            'connectors.gender',
            'chats.id as chat_id'
        )
        ->join('connectors', 'connectors.id', '=', 'work_connections.connector_id')
        ->leftjoin('chats', function ($join) {
            $join->on('work_connections.connector_id', '=', 'chats.connector_id');
            $join->on('work_connections.job_id', '=', 'chats.job_id');
        })
        ->orderBy('id', 'DESC')
        ->where('work_connections.job_id', $job_id);
    
        $result = $result ->where(function ($query) use ($keyword) {
            if (!empty($keyword)) {
                $query->where('connectors.username', 'LIKE', '%' . $keyword . '%');
                $query->orwhere('connectors.email', 'LIKE', '%' . $keyword . '%');
            }
        });
        if (!is_null($status) && $status != -1) {
            $result->where('work_connections.status', $status);
        }
        if ($status == -1) {
            $result->where('work_connections.is_new', 1);
        }
        $count = $result->count();
        $result->offset(($page_number - 1) * $page_limit)
            ->limit($page_limit);
        return array('total' => $count, 'data' => $result->get());
    }

    public static function getNewByStaff($company_id, $page_limit, $page_number, $keyword)
    {
        $result = WorkConnection::select(
            'work_connections.id',
            'work_connections.job_id',
            'work_connections.connector_id',
            'work_connections.status',
            'work_connections.created_at as apply_date',
            'work_connections.is_new',
            'connectors.username',
            'connectors.email',
            'connectors.phone_number',
            'connectors.birthday',
            'connectors.gender',
            'jobs.title as job_title',
            'jobs.company_id',
            'chats.id as chat_id'
        )
        ->join('connectors', 'connectors.id', '=', 'work_connections.connector_id')
        ->join('jobs', 'jobs.id', '=', 'work_connections.job_id')
        ->leftjoin('chats', function ($join) {
            $join->on('work_connections.connector_id', '=', 'chats.connector_id');
            $join->on('work_connections.job_id', '=', 'chats.job_id');
        })
        ->orderBy('id', 'DESC')
        ->where(function ($query) {
            $query->where('work_connections.is_new', 1)
                ->orWhere('work_connections.status', 0);
        })
        ->where('jobs.company_id', $company_id);
    
        $result = $result ->where(function ($query) use ($keyword) {
            if (!empty($keyword)) {
                $query->where('connectors.username', 'LIKE', '%' . $keyword . '%');
                $query->orwhere('connectors.email', 'LIKE', '%' . $keyword . '%');
            }
        });
        $count = $result->count();
        $result->offset(($page_number - 1) * $page_limit)
            ->limit($page_limit);
        return array('total' => $count, 'data' => $result->get());
    }
    public static function countNew($company_id)
    {
        $result = WorkConnection::select(
            'work_connections.id'
        )
        ->join('jobs', 'jobs.id', '=', 'work_connections.job_id')
        ->where('work_connections.is_new', 1)
        ->where('jobs.company_id', $company_id);
    
        $count = $result->count();
        return $count;
    }

    public static function checkWorkConnection($id)
    {
        $workConnection = WorkConnection::find($id);
        if (empty($workConnection)) {
            return false;
        }
        return true;
    }

    public static function updateIsNew($id)
    {
        $workConnection = WorkConnection::find($id);
        $workConnection->is_new = 0;
        $workConnection->save();
        return $workConnection->id;
    }

    public static function detailWorkConnection($id)
    {
        $workConnection = WorkConnection::select(
            'work_connections.*',
            'applicant.username',
            'applicant.email',
            'applicant.phone_number',
            'applicant.birthday',
            'applicant.gender',
            'applicant.address',
            'applicant.avatar',
            'applicant.current_work',
            'applicant.current_work_place',
            'connector.id as connector_id',
            'connector.username as connector_username',
            'connector.gender as connector_gender',
            'connector.avatar as connector_avatar'
        )
        ->join('connectors as applicant', 'applicant.id', '=', 'work_connections.connector_id')
        ->leftjoin('connectors as connector', 'work_connections.introduction_id', '=', 'connector.connector_code')
        ->where('work_connections.id', $id)->first();
        return $workConnection;
    }



    public static function getListJobsAppliedByConnectorId($connector_id, $page_number, $page_limit)
    {
        $result = WorkConnection::select(
            'jobs.id',
            'jobs.title',
            'work_connections.status',
            'work_connections.created_at',
            'categories.category_name',
            'chats.id AS chat_id',
            'favorites.is_favorite'
        )
        ->join('connectors', 'connectors.id', '=', 'work_connections.connector_id')
        ->join('jobs', 'jobs.id', '=', 'work_connections.job_id')
        ->leftjoin('categories', function ($join) {
            $join->on('jobs.category_id', '=', 'categories.id');
        })
        ->leftjoin('favorites', function ($join) use ($connector_id) {
            $join->on('favorites.job_id', '=', 'jobs.id');
            $join->where('favorites.connector_id', '=', $connector_id);
        })
        ->leftjoin('chats', function ($join) use ($connector_id) {
            $join->on('chats.job_id', '=', 'jobs.id');
            $join->where('chats.connector_id', '=', $connector_id);
        })
        ->where('work_connections.connector_id', $connector_id);

        $ary_data_tmp = array();
        $ary_data = array();
        $ary_data_result = array();

        $ary_data_result['total_date'] = 0;
        $ary_data_result['data'] = array();
        $result = $result->orderBy('work_connections.id', 'desc')->get();
        $count_date = 0;
        $from_count = ($page_number - 1) * $page_limit;
        $to_count = $from_count + $page_limit;
        if (count($result) > 0) {
            for ($i = 0; $i < count($result); $i++) {
                $result[$i]['main_image'] = JobFile::getMainImageJob($result[$i]['id']);
                $result[$i]['base_path'] = Job::getBasePathJob();
                $result[$i]['date_at'] = $result[$i]['created_at']->year . '年' . $result[$i]['created_at']->month . '月' . $result[$i]['created_at']->day . '日';
                unset($result[$i]['created_at']);
            }
            foreach ($result as $element) {
                $ary_data[$element['date_at']][] = $element;
            }
            foreach ($ary_data as $key => $value) {
                if ($from_count <= $count_date && $count_date < $to_count) {
                    $ary_data_tmp['date'] = $key;
                    $ary_data_tmp['jobs'] = $value;

                    $ary_data_result['data'][] = $ary_data_tmp;
                }
                $ary_data_result['total_date']++;
                $count_date++;
            }
        }

        return $ary_data_result;
    }

    public static function getIdByConnectorIdAndJobId($connector_id, $job_id)
    {
        $result = WorkConnection::select('id')
        ->where('connector_id', $connector_id)
        ->where('job_id', $job_id)
        ->first();
        if (!empty($result)) {
            return $result->id;
        } else {
            return null;
        }
    }

    public static function checkAppliedJob($connector_id, $job_id)
    {
        $result = WorkConnection::select('id')
        ->where('connector_id', $connector_id)
        ->where('job_id', $job_id)
        ->first();
        if (!empty($result)) {
            return false;
        }
        return true;
    }
}
