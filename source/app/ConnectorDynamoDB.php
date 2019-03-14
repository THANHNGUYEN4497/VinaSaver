<?php

namespace App;

use BaoPham\DynamoDb\DynamoDbModel;

class ConnectorDynamoDB extends DynamoDbModel
{
    //
    protected $table = 'ConnectorList';
    protected $primaryKey = 'connectorId';
    protected $fillable = [
        'connectorId','apiToken',
    ];
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = app()->environment() . '_' . $this->table;
    }
    public static function add($connector)
    {
        $connector_dynamo = new ConnectorDynamoDB();

        $connector_dynamo->connectorId = $connector->id;
        $connector_dynamo->apiToken = $connector->api_token;

        $connector_dynamo->save();
        return $connector_dynamo->connectorId;
    }
    public static function remove($connector_id)
    {
        return ConnectorDynamoDB::destroy(intval($connector_id));
    }
}
