<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Carbon\Carbon;
use App\Staff;
use App\StaffDynamoDB;

use Illuminate\Support\Facades\DB;

class CompanyLoginLogoutApiTest extends TestCase
{
    /**
     * /company/login
     *
     * @return void
     */
    public function testLogin()
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

        //clean-up
        $resJsonObject = json_decode($response->content());
        $staff_id = $resJsonObject->data->id;
        Staff::find($staff_id)->logout();
    }
    /**
     * /company/logout
     *
     * @return void
     */
    public function testLogout()
    {
        //pre
        $staff = Staff::where('email','d.sogo@crepus.jp')->first();
        print $staff->login('some_token');
        $staff->login('some_token');
        //logout
        $response = $this->post(
            '/api/company/logout',
            [
                'api_token' => $staff->api_token,
                'staff_id' => $staff->id,
            ]
        );
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }
}
