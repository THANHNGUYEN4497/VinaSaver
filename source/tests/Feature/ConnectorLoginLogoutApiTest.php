<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Connector;
use App\ConnectorDynamoDB;

use Illuminate\Support\Facades\DB;

class ConnectorLoginLogoutApiTest extends TestCase
{
    protected $connector_id;
    public function setUp()
    {
        parent::setUp();
        $this->connector_id = Connector::add(
            '0123456789',
            'testpass',
            '',
            1,
            ''
        );
        Connector::changeAvailableStatus($this->connector_id, 1);
    }
    public function tearDown()
    {
        Connector::deleteById($this->connector_id);
        $max = DB::table('connectors')->max('id') + 1;
        DB::statement("ALTER TABLE connectors AUTO_INCREMENT =  $max");
    }
    /**
     * /connector/login
     *
     * @return void
     */
    public function testLogin()
    {
        //login
        $response = $this->post(
            '/api/connector/login',
            [
                'phone_number' => '0123456789',
                'password' => 'testpass'
            ]
        );
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }
    /**
     * /company/logout
     *
     * @return void
     */
    public function _testLogout()
    {
        //pre
        $connector = Connector::where('phone_number','0123456789')->first();
        $connector->login('some_token');
        //logout
        $response = $this->post(
            '/api/connector/logout',
            [
                'api_token' => $connector->api_token,
                'connector_id' => $connector->id,
            ]
        );
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }
}
