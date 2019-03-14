<?php

namespace App;

use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $dateFormat = 'U';
    protected $fillable = [
        'category_name',
    ];

    public static function getList()
    {
        $result = Category::select('id', 'category_name')->get();
        return $result;
    }
}
