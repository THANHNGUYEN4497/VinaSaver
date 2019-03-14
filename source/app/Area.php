<?php

namespace App;

use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'areas';
    protected $dateFormat = 'U';
    protected $fillable = [
        'area_name',
    ];

    public static function getList()
    {
        $result = Area::select('id', 'area_name')->get();
        return $result;
    }
}
