<?php

namespace Tests\Feature\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Connector;

use Illuminate\Support\Facades\DB;

class ConnectorSignupApiTest extends TestCase
{
    protected $connector_id;
    public function tearDown()
    {
        Connector::deleteById($this->connector_id);
        $max = DB::table('connectors')->max('id') + 1;
        DB::statement("ALTER TABLE connectors AUTO_INCREMENT =  $max");
    }
    /**
     * /api/connector/register
     *
     * @return void
     */
    public function testSignup_NoRequiredParameters()
    {
        $response = $this->post(
            '/api/connector/register',
            [
            ]
        );
        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'data' => null,
            'error' => 'common_message.error.MISS_PARAM',
        ]);
    }
    public function testSignup_NG_Duplicate()
    {
        $this->connector_id = Connector::add(
            '0123456789',
            'testpass',
            '',
            1,
            ''
        );
        $response = $this->post(
            '/api/connector/register',
            [
                'phone_number' => '0123456789',
                'password' => 'testpass',
            ]
        );
        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'data' => null,
            'error' => 'アカウントが既に存在しています。',
        ]);
    }
    public function testSignup_OK()
    {
        $response = $this->post(
            '/api/connector/register',
            [
                'phone_number' => '0123456789',
                'password' => 'testpass',
            ]
        );
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $resJsonObject = json_decode($response->content());
        $this->connector_id = $resJsonObject->data;
    }
    /**
     * /connector/verify
     *
     * @return void
     */
    public function testVerify_NG()
    {
        $this->connector_id = Connector::add(
            '0123456789',
            'testpass',
            '',
            1,
            ''
        );
        $response = $this->post(
            '/api/connector/verify',
            [
                'phone_number' => '0123456789',
                'auth_number' => '00000',
                'type' => 1,
            ]
        );
        $response->assertStatus(200);
        $response->assertExactJson([
            'success' => false,
            'data' => null,
            'error' => json_decode('{
                "auth_number": ["The auth number must be at least 10000."]
            }')
        ]);
    }
    /**
     * /connector/verify
     *
     * @return void
     */
    public function testVerify_OK()
    {
        $this->connector_id = Connector::add(
            '0123456789',
            'testpass',
            '',
            1,
            ''
        );
        $response = $this->post(
            '/api/connector/verify',
            [
                'phone_number' => '0123456789',
                'auth_number' => '99999',
                'type' => 1,
            ]
        );
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $this->connector_id,
                'username' => null,
                'email' => null,
                'phone_number' => '0123456789',
                'birthday' => null,
                'gender' => 1,
                'available_status' => 1,
            ],
        ]);
    }
}
