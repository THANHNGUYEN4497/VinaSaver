<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Favorite extends Model
{
    protected $table = 'favorites';
    protected $fillable = ['connector_id', 'job_id', 'is_favorite', 'del'];
    protected $dateFormat = 'U';
    public $timestamps = true;

    public static function add($job_id, $connector_id, $is_favorite)
    {
        $result = null;
        $check = Favorite::checkJobConnectorExist($job_id, $connector_id);
        if (!empty($check)) {
            $result = Favorite::edit($check, $job_id, $connector_id, $is_favorite);
        } else {
            $result = DB::table('favorites')->insertGetId(array(
                        'job_id' => $job_id,
                        'connector_id' => $connector_id,
                        'is_favorite' => $is_favorite,
                        'created_at' => now()->timestamp,
                        'updated_at' => now()->timestamp
                        ));
        }
        return $result;
    }

    public static function checkJobConnectorExist($job_id, $connector_id)
    {
        $result = Favorite::where('job_id', $job_id)
                        ->where('connector_id', $connector_id)
                        ->first();
        if (!empty($result)) {
            return $result->id;
        } else {
            return null;
        }
    }

    public static function edit($id, $job_id, $connector_id, $is_favorite)
    {
        $info = Favorite::find($id);
        if ($info) {
            if (!empty($job_id)) {
                $info->job_id = $job_id;
            }
            if (!empty($connector_id)) {
                $info->connector_id = $connector_id;
            }
            if (!is_null($is_favorite)) {
                $info->is_favorite = $is_favorite;
            }
            $info->save();
            return $info->id;
        } else {
            return null;
        }
    }

    public static function getFavoriteJob($connector_id, $page_number, $page_limit)
    {
        $page_number = ($page_number - 1) * $page_limit;
        $query = Favorite::select(
            'jobs.id',
            'jobs.title',
            'jobs.category_id',
            'categories.category_name',
            'jobs.salary',
            'favorites.is_favorite'
        );
        $result = $query->leftjoin('jobs', 'jobs.id', '=', 'favorites.job_id')
                    ->leftjoin('categories', 'jobs.category_id', '=', 'categories.id')
                    ->where('favorites.connector_id', $connector_id)
                    ->where('favorites.is_favorite', 1);
        $data = array();
        $data['total_items'] = $result->count();

        $result = $result->orderBy('favorites.id', 'desc')
            ->offset($page_number)
            ->limit($page_limit)
            ->get();
        $data['data'] = $result;
        if (count($data['data']) > 0) {
            for ($i = 0; $i < count($data['data']); $i++) {
                $data['data'][$i]['main_image'] = JobFile::getMainImageJob($data['data'][$i]['id']);
                $data['data'][$i]['base_path'] = Job::getBasePathJob();
            }
        }

        return $data;
    }
}
