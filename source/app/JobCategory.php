<?php

namespace App;

use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class JobCategory extends Model
{
    protected $table = 'job_categories';
    protected $dateFormat = 'U';
    protected $fillable = [
        'job_category_name',
    ];

    public static function getList()
    {
        $result = JobCategory::select('id', 'job_category_name')->get();
        return $result;
    }
}
