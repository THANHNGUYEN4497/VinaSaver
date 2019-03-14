<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class IntroductionStatus extends Model
{
    protected $table = 'introduction_status';
    protected $dateFormat = 'U';
    //status=0: not request, status = 1: request by connector
    //type = 1: registered by introduction code, type = 2: apply job by introduction code, type = 3: recruited by introduction code
    protected $fillable = [
        'connector_id','introduction_id', 'job_id', 'type', 'amount', 'content', 'status'
    ];

    public static function add($connector_id, $introduction_id, $job_id, $type, $amount, $content, $status)
    {
        $result = null;
        $result = DB::table('introduction_status')->insertGetId(array(
                'connector_id' => $connector_id,
                'introduction_id' => $introduction_id,
                'job_id' => $job_id,
                'type' => $type,
                'amount' => $amount,
                'content' => $content,
                'status' => $status,
                'created_at' => now()->timestamp,
                'updated_at' => now()->timestamp
                ));
        
        return $result;
    }

    public static function edit($id, $status)
    {
        $info = IntroductionStatus::find($id);
        if ($info) {
            if (!is_null($status)) {
                $info->status = $status;
            }
            $info->save();
            return $info->id;
        } else {
            return null;
        }
    }

    public static function edit_status_by_introduction_id($introduction_id)
    {
        $result = DB::table('introduction_status')
                ->where('introduction_id', $introduction_id)
                ->where('status', 0)
                ->update([ 'status' => 1]);
        return $result;
    }

    public static function getListIntroductionStatus($introduction_id, $page_number, $page_limit)
    {
        $result = IntroductionStatus::select(
            'introduction_status.id',
            'introduction_status.status',
            'introduction_status.type',
            'introduction_status.amount',
            'introduction_status.content',
            'connectors.id AS id_friend',
            'connectors.username',
            'connectors.avatar as image_connector',
            'companies.company_name',
            'companies.id as company_id',
            'jobs.title'
        )
                                    ->leftjoin('connectors', 'connectors.id', '=', 'introduction_status.connector_id')
                                    ->leftjoin('jobs', 'jobs.id', '=', 'introduction_status.job_id')
                                    ->leftjoin('companies', 'companies.id', '=', 'jobs.company_id')
                                    ->where('introduction_status.introduction_id', $introduction_id)
                                    ->orderBy('introduction_status.created_at', 'desc');
        $data = array();
        $data['total_item'] = $result->count();
        $data['data'] = $result->offset(($page_number - 1) * $page_limit)->limit($page_limit)->get();
        if (count($data['data']) > 0) {
            for ($i = 0; $i < count($data['data']); $i++) {
                $data['data'][$i]['image_company'] = CompanyFile::getMainImage($data['data'][$i]['company_id']);
                $data['data'][$i]['base_path_company'] = Company::getBasePath();
                $data['data'][$i]['base_path_connector'] = Connector::getBasePath();
            }
        }
        
        return $data;
    }

    public static function getListIntroductionStatusByIntroductionId($introduction_id)
    {
        $query = IntroductionStatus::select(
            'introduction_status.id',
            'introduction_status.connector_id',
            'introduction_status.job_id',
            'introduction_status.type',
            'introduction_status.introduction_id'
        );
        $result = $query->where('introduction_status.introduction_id', $introduction_id)
                        ->where('introduction_status.status', 0)
                        ->orderBy('introduction_status.id', 'desc')
                        ->get();
        return $result;
    }

    public static function getListCompensationOccurrenceHistory($introduction_id, $page_number, $page_limit)
    {
        $query = IntroductionStatus::select(
            'introduction_status.id',
            'introduction_status.status',
            'introduction_status.type',
            'introduction_status.amount',
            'introduction_status.content',
            'introduction_status.created_at',
            'connectors.id AS id_friend',
            'connectors.username',
            'connectors.avatar',
            'companies.company_name',
            'companies.id AS company_id',
            'jobs.title'
        )
                                    ->leftjoin('connectors', 'connectors.id', '=', 'introduction_status.connector_id')
                                    ->leftjoin('jobs', 'jobs.id', '=', 'introduction_status.job_id')
                                    ->leftjoin('companies', 'companies.id', '=', 'jobs.company_id')
                                    ->where('introduction_status.introduction_id', $introduction_id)
                                    ->orderBy('introduction_status.created_at', 'desc');
        $data = array();
        $data['total_item'] = $query->count();
        $data['data'] = $query->offset(($page_number - 1) * $page_limit)->limit($page_limit)->get();
        if (count($data['data']) > 0) {
            for ($i = 0; $i < count($data['data']); $i++) {
                // $data['data'][$i]['image_company'] = CompanyFile::getMainImage($data['data'][$i]['company_id']);
                // $data['data'][$i]['base_path_company'] = Company::getBasePath();
                $data['data'][$i]['base_path_connector'] = Connector::getBasePath();
            }
        }
        
        return $data;
    }
}
