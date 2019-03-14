<?php

namespace App;

use BaoPham\DynamoDb\DynamoDbModel;

class CompanyDynamoDB extends DynamoDbModel
{
    //
    protected $table = 'CompanyList';
    protected $primaryKey = 'companyId';
    protected $fillable = [
        'companyId',
    ];
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = app()->environment() . '_' . $this->table;
    }
    public static function add($company_id)
    {
        $company = new CompanyDynamoDB();

        $company->companyId = $company_id;
        $company->save();
        return $company->companyId;
    }
    public static function remove($company_id)
    {
        return CompanyDynamoDB::destroy(intval($company_id));
    }
}
