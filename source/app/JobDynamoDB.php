<?php

namespace App;

use BaoPham\DynamoDb\DynamoDbModel;

class JobDynamoDB extends DynamoDbModel
{
    //
    protected $table = 'JobList';
    protected $primaryKey = 'jobId';
    protected $fillable = [
        'jobId',
    ];
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = app()->environment() . '_' . $this->table;
    }
    public static function add($job_id)
    {
        $job = new JobDynamoDB();

        $job->jobId = $job_id;
        $job->save();
        return $job->jobId;
    }
    public static function remove($job_id)
    {
        return JobDynamoDB::destroy(intval($job_id));
    }
}
