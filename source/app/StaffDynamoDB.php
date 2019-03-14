<?php

namespace App;

use BaoPham\DynamoDb\DynamoDbModel;

class StaffDynamoDB extends DynamoDbModel
{
    //
    protected $table = 'StaffList';
    protected $primaryKey = 'staffId';
    protected $fillable = [
        'staffId','companyId','apiToken',
    ];
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = app()->environment() . '_' . $this->table;
    }
    public static function add($staff)
    {
        $staff_dynamo = new StaffDynamoDB();

        $staff_dynamo->staffId = $staff->id;
        $staff_dynamo->companyId = $staff->company_id;
        $staff_dynamo->apiToken = $staff->api_token;

        $staff_dynamo->save();
        return $staff_dynamo->staffId;
    }
    public static function remove($staff_id)
    {
        return StaffDynamoDB::destroy(intval($staff_id));
    }
}
