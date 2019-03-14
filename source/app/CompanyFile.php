<?php

namespace App;

use Illuminate\Support\Facades\Auth;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class CompanyFile extends Authenticatable
{
    protected $table = 'company_files';
    protected $dateFormat = 'U';
    public $timestamps = true;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'content_id', 'company_id', 'path', 'type'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    // protected  $primaryKey = 'content_id';

    public static function addFile($content_id, $company_id, $path, $type)
    {
        $company_file = new CompanyFile();
        $company_file->content_id = $content_id;
        $company_file->company_id = $company_id;
        $company_file->path = $path;
        $company_file->type = $type;
        $company_file->save();
        return $company_file->id;
    }

    public static function updateFile($id, $path)
    {
        $company_file = CompanyFile::find($id);
        $company_file->path = $path;
        $company_file->save();
        return $company_file->id;
    }

    public static function deleteFile($id)
    {
        CompanyFile::destroy($id);
        return true;
    }

    public static function checkExist($id)
    {
        $company_file = CompanyFile::find($id);
        if (empty($company_file)) {
            return false;
        }
        return true;
    }

    public static function getCompanyFile($company_id, $type)
    {
        $result = CompanyFile::select('id', 'path')
            ->where('company_id', $company_id)
            ->where('type', $type)
            ->orderBy('id', 'asc')
            ->get();
        return $result;
    }

    public static function deleteFileCompany($company_id)
    {
        $paths = CompanyFile::where('company_id', $company_id)->get(['path']);
        $file_ids = CompanyFile::where('company_id', $company_id)->get(['id']);
        CompanyFile::destroy($file_ids->toArray());
        return $paths;
    }

    public static function detailFile($id)
    {
        return CompanyFile::find($id);
    }

    public static function getMainImage($company_id)
    {
        $result = CompanyFile::select('path')
            ->where('company_id', $company_id)
            ->where('type', 1)
            ->orderBy('created_at', 'asc')
            ->first();
        if (!empty($result)) {
            return $result->path;
        } else {
            return null;
        }
    }
}
