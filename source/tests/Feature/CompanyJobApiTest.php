<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Carbon\Carbon;
use App\Company;
use App\Job;
use App\JobDynamoDB;

use Illuminate\Support\Facades\DB;

class CompanyJobApiTest extends TestCase
{
    public function tearDown()
    {
        $max = DB::table('jobs')->max('id') + 1;
        DB::statement("ALTER TABLE jobs AUTO_INCREMENT =  $max");
    }
    /**
     * /company/job/add
     *
     * @return void
     */
    public function testJobAdd_NoRequiredParameter()
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
            '/api/company/job/add',
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
     * /company/job/add
     *
     * @return void
     */
    public function testJobAdd_OK_NoFile()
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
            '/api/company/job/add',
            [
                'api_token' => $api_token,
                'title' => 'aaa',
                'staff_id' => 1,
                'company_id' => 1,
                'category_id' => 1,
                'job_category_id' => 1,
                'job_type_id' => 1,
                'area_id' => 1,
                'age_min' => 20,
                'age_max' => 40,
                'gender_ratio' => 1,
                'management_staff' => 1,
                'hours' => 9,
                'release_start_date' => '2019-02-06 15:00',
                'release_end_date' => '2019-02-10 15:00'
            ]
        );
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        //clean-up
        $resJsonObject = json_decode($response->content());
        $job_id = $resJsonObject->data;
        JobDynamoDB::find($job_id)->delete();
        Job::where('id',$job_id)->delete();
    }
    /**
     * /company/job/delete
     *
     * @return void
     */
    public function testJobDelete_NoRequiredParameter()
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
            '/api/company/job/delete/2',
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
     * /company/job/delete
     *
     * @return void
     */
    public function testJobDelete_OK()
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
        $job = new Job();
        $job->id = 1;
        $job->title = 'aaa';
        $job->company_id = 1;
        $job->staff_id = 1;
        $job->category_id = 1;
        $job->job_category_id = 1;
        $job->job_type_id = 1;
        $job->area_id = 1;
        $job->description = 'aaa';
        $job->address = 'aaa';
        $job->salary = 100;
        $job->age_max = 99;
        $job->age_min = 20;
        $job->gender_ratio = 1;
        $job->traffic = 'aaa';
        $job->introduction_title = 'aaaa';
        $job->introduction_content = 'bbbb';
        $job->job_content = 'aaaa';
        $job->store_name = 'aaaa';
        $job->workplace_status = 1;
        $job->hours = 10;
        $job->working_time = 8;
        $job->welcome = 'aaaa';
        $job->requirements = 'nothing';
        $job->release_start_date = Carbon::parse("2019-02-06 15:00")->timestamp;
        $job->release_end_date = Carbon::parse("2019-02-10 15:00")->timestamp;
        $job->management_staff = 1;
        $job_id = $job->save();
        //
        $response = $this->post(
            '/api/company/job/delete/1',
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
