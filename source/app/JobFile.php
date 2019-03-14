<?php

namespace App;

use Illuminate\Support\Facades\Auth;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class JobFile extends Authenticatable
{
    protected $table = 'job_files';
    protected $dateFormat = 'U';
    public $timestamps = true;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'content_id','job_id', 'path', 'type'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public static function addFile($content_id, $job_id, $path, $type)
    {
        $job_file = new JobFile();
        $job_file->content_id = $content_id;
        $job_file->job_id = $job_id;
        $job_file->path = $path;
        $job_file->type = $type;
        $job_file->save();
        return $job_file->id;
    }

    public static function updateFile($id, $path)
    {
        $job_file = JobFile::find($id);
        $job_file->path = $path;
        $job_file->save();
        return $job_file->id;
    }

    public static function deleteFile($id)
    {
        JobFile::destroy($id);
        return true;
    }

    public static function checkExist($id)
    {
        $job_file = JobFile::find($id);
        if (empty($job_file)) {
            return false;
        }
        return true;
    }

    public static function getJobFile($job_id, $type)
    {
        $result = JobFile::select('id', 'path')
            ->where('job_id', $job_id)
            ->where('type', $type)
            ->orderBy('id', 'asc')
            ->get();
        return $result;
    }

    public static function getDetailImageJob($job_id)
    {
        $result = JobFile::select('path')
            ->where('job_id', $job_id)
            ->where('type', 1)
            ->orderBy('created_at', 'asc')
            ->get();
        $ary_data = array();
        if (count($result) > 0) {
            for ($i = 0; $i < count($result); $i++) {
                $ary_data[] = $result[$i]['path'];
            }
        }
        return $ary_data;
    }

    public static function getDetailVideoJob($job_id)
    {
        $result = JobFile::select('path')
            ->where('job_id', $job_id)
            ->where('type', 2)
            ->orderBy('id', 'desc')
            ->first();
        if (!empty($result->path)) {
            return $result->path;
        }
        return null;
    }

    public static function getMainImageJob($job_id)
    {
        $result = JobFile::select('path')
            ->where('job_id', $job_id)
            ->where('type', 1)
            ->orderBy('created_at', 'asc')
            ->first();
        if (!empty($result)) {
            return $result->path;
        } else {
            return null;
        }
    }

    public static function deleteFileJob($job_id)
    {
        $paths = JobFile::where('job_id', $job_id)->get(['path']);
        $file_ids = JobFile::where('job_id', $job_id)->get(['id']);
        JobFile::destroy($file_ids->toArray());
        return $paths;
    }

    public static function detailFile($id)
    {
        return JobFile::find($id);
    }
}
