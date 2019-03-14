<?php

namespace App;

use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class JobType extends Model
{
    protected $table = 'job_types';
    protected $dateFormat = 'U';
    protected $fillable = [
        'type_name',
    ];

    public static function getList()
    {
        $result = JobType::select('id', 'type_name')->get();
        return $result;
    }
}
