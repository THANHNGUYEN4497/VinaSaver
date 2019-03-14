<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Carbon\Carbon;
use App\Staff;
use App\StaffDynamoDB;

use Illuminate\Support\Facades\DB;

class CompanyStaffApiTest extends TestCase
{
    public function tearDown()
    {
        $max = DB::table('staffs')->max('id') + 1;
        DB::statement("ALTER TABLE staffs AUTO_INCREMENT =  $max");
    }
    /**
     * /company/staff/add
     *
     * @return void
     */
    public function testStaffAdd_NoRequiredParameter()
    {
        //login
        $response = $this->post(
            '/api/company/login',
            [
                'email' => 'd.sogo@crepus.jp',
                'password' => 'testpass'
            ]
        );
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $resJsonObject = json_decode($response->content());
        $api_token = $resJsonObject->data->api_token;
        //
        $response = $this->post(
            '/api/company/staff/add',
            [
                'api_token' => $api_token,
            ]
        );
        $response->assertStatus(200);
        $response->assertExactJson([
            'success' => false,
            'data' => null,
            'error' => "common_message.error.MISS_PARAM"
        ]);
    }
    /**
     * /company/staff/add
     *
     * @return void
     */
    public function testStaffAdd_OK()
    {
        //login
        $response = $this->post(
            '/api/company/login',
            [
                'email' => 'd.sogo@crepus.jp',
                'password' => 'testpass'
            ]
        );
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $resJsonObject = json_decode($response->content());
        $api_token = $resJsonObject->data->api_token;
        //
        $response = $this->post(
            '/api/company/staff/add',
            [
                'api_token' => $api_token,
                'email' => 'popai@crepus.jp',
                'password' => bcrypt('testpass'),
                'phone_number' => '1234567890',
                'username' => 'aaa',
                'position' => 1,
                'office' => 'aaaaaaa',
            ]
        );
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        //clean-up
        $resJsonObject = json_decode($response->content());
        $staff_id = $resJsonObject->data;
        Staff::remove($staff_id);
    }
    /**
     * /company/staff/delete
     *
     * @return void
     */
    public function testStaffDelete_NoRequiredParameter()
    {
        //login
        $response = $this->post(
            '/api/company/login',
            [
                'email' => 'd.sogo@crepus.jp',
                'password' => 'testpass'
            ]
        );
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $resJsonObject = json_decode($response->content());
        $api_token = $resJsonObject->data->api_token;
        //
        $response = $this->post(
            '/api/company/staff/delete/999',
            [
                'api_token' => $api_token,
            ]
        );
        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'data' => null,
            'error' => "オブジェクトが存在しません",
        ]);
    }
    /**
     * /company/staff/delete
     *
     * @return void
     */
    public function testStaffDelete_OK()
    {
        //login
        $response = $this->post(
            '/api/company/login',
            [
                'email' => 'd.sogo@crepus.jp',
                'password' => 'testpass'
            ]
        );
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $resJsonObject = json_decode($response->content());
        $api_token = $resJsonObject->data->api_token;
        //
        $staff_id = Staff::add(
            1,
            'nakata@crepus.jp',
            bcrypt('testpass'),
            '中田',
            '01234567890',
            'aaaa',
            1
        );
        //
        $response = $this->post(
            '/api/company/staff/delete/'.$staff_id,
            [
                'api_token' => $api_token,
            ]
        );
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }
}
