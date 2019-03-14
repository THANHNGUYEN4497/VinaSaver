<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Company;
use App\CompanyDynamoDB;
use App\Staff;
use App\StaffDynamoDB;

use Illuminate\Support\Facades\DB;

class AdminCompanyApiTest extends TestCase
{
    public function tearDown()
    {
        $max = DB::table('companies')->max('id') + 1;
        DB::statement("ALTER TABLE companies AUTO_INCREMENT =  $max");
        $max = DB::table('staffs')->max('id') + 1;
        DB::statement("ALTER TABLE staffs AUTO_INCREMENT =  $max");
    }
    /**
     * /admin/company/add
     *
     * @return void
     */
    public function testCompanyAdd_NoRequiredParameter()
    {
        //login
        $response = $this->post(
            '/api/admin/login',
            [
                'email' => 'd.sogo@crepus.jp',
                'password' => '123456789'
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
            '/api/admin/company/add',
            [
                'api_token' => $api_token,
            ]
        );
        $response->assertStatus(200);
        $response->assertExactJson([
            'success' => false,
            'data' => null,
            'error' => json_decode('{
                "address": ["The address field is required."],
                "admin_id": ["The admin id field is required."],
                "company_name": ["The company name field is required."],
                "email": ["The email field is required."],
                "password": ["The password field is required."],
                "phone_number": ["The phone number field is required."],
                "username_staff": ["The username staff field is required."]
            }')
        ]);
    }
    /**
     * /company/job/add
     *
     * @return void
     */
    public function testCompanyAdd_OK_NoFile()
    {
        //login
        $response = $this->post(
            '/api/admin/login',
            [
                'email' => 'd.sogo@crepus.jp',
                'password' => '123456789'
            ]
        );
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $resJsonObject = json_decode($response->content());
        $api_token = $resJsonObject->data->api_token;
        $admin_id = $resJsonObject->data->id;
        //
        $response = $this->post(
            '/api/admin/company/add',
            [
                'api_token' => $api_token,
                'admin_id' => $admin_id,
                'company_name' => 'foo',
                'address' => '愛媛県松山市~~~~3F',
                'phone_number' => '0123456789',
                'email' => 'test@crepus.jp',
                'url' => 'https://locofull.jp',
                'agency_name' => '中田',
                'business_field' => 1,
                'latitude' => 12.3,
                'longitude' => 45.6,
                'password' => '123456789',
                'password_confirmation' => '123456789',  //TODO: confirmationはクライアントでやってもらう
                'username_staff' => 'aaa',
                'email_staff' => 'koko@crepus.jp',
            ]
        );
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        //clean-up
        $resJsonObject = json_decode($response->content());
        $company_id = $resJsonObject->data;
        Company::deleteById($company_id);
        Staff::where('company_id',$company_id)->delete();   //付随して,staffが作られる為
    }
    /**
     * /admin/company/delete
     *
     * @return void
     */
    public function testCompanyDelete_OK()
    {
        //login
        $response = $this->post(
            '/api/admin/login',
            [
                'email' => 'd.sogo@crepus.jp',
                'password' => '123456789'
            ]
        );
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $resJsonObject = json_decode($response->content());
        $api_token = $resJsonObject->data->api_token;
        $admin_id = $resJsonObject->data->id;
        //
        $company_id = Company::store([
            'id' => DB::table('companies')->max('id') + 1,
            'admin_id' => 2,
            'company_name' => 'test',
            'address' => 'aaa',
            'email' => 'test@crepus.jp',
            'phone_number' => '0123456789',
            'url' => 'https://locofull.jp',
            'agency_name' => '中田',
            'business_field' => 2,
            'latitude' => 12.3,
            'longitude' => 45.6,
            'introduction' => 'aaa',
        ]);
        $staff_id = Staff::store([
            'email' => 'nakata@crepus.jp',
            'password' => bcrypt('testpass'),
            'company_id' => $company_id,
            'privilege' => 1,
            'username' => '中田',
        ]);

        $response = $this->post(
            '/api/admin/company/delete/'.$company_id,
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
