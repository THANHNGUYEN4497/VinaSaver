<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationTest extends TestCase
{
    public function testNotAuthenticated()
    {
        //admin
        $response = $this->post('/api/admin/login');
        $response->assertStatus(200);

        //company
        $response = $this->post('/api/company/login');
        $response->assertStatus(200);

        //app
        $response = $this->post('/api/connector/login');
        $response->assertStatus(200);

        $response = $this->post('/api/connector/forgot-password');
        $response->assertStatus(200);

        $response = $this->post('/api/connector/verify');
        $response->assertStatus(200);

        $response = $this->post('/api/connector/register');
        $response->assertStatus(200);

        $response = $this->post('/api/connector/reset-password');
        $response->assertStatus(200);
    }

    public function testAuthenticatedForAdminPart()
    {
        $response = $this->post('/api/admin/logout');
        $response->assertStatus(401);

        $response = $this->get('/api/admin/connector/list');
        $response->assertStatus(401);

        $response = $this->get('/api/admin/connector/detail/1');
        $response->assertStatus(401);
        
        $response = $this->post('/api/admin/connector/delete/1');
        $response->assertStatus(401);

        $response = $this->get('/api/admin/job/list');
        $response->assertStatus(401);

        $response = $this->get('/api/admin/job/detail/1');
        $response->assertStatus(401);

        $response = $this->post('/api/admin/job/delete/1');
        $response->assertStatus(401);

        $response = $this->get('/api/admin/job/category');
        $response->assertStatus(401);

        $response = $this->get('/api/admin/job/applicant/list');
        $response->assertStatus(401);

        $response = $this->get('/api/admin/job/applicant/detail/1');
        $response->assertStatus(401);

        $response = $this->get('/api/admin/company/list');
        $response->assertStatus(401);

        $response = $this->get('/api/admin/company/detail/1');
        $response->assertStatus(401);

        $response = $this->post('/api/admin/company/add');
        $response->assertStatus(401);

        $response = $this->post('/api/admin/company/edit/1');
        $response->assertStatus(401);

        $response = $this->post('/api/admin/company/delete/1');
        $response->assertStatus(401);

        $response = $this->get('/api/admin/staff/list');
        $response->assertStatus(401);

        $response = $this->get('/api/admin/staff/detail/1');
        $response->assertStatus(401);

        $response = $this->get('/api/admin/payment/list');
        $response->assertStatus(401);

        $response = $this->post('/api/admin/add');
        $response->assertStatus(401);

        $response = $this->get('/api/admin/list');
        $response->assertStatus(401);

        $response = $this->get('/api/admin/detail/1');
        $response->assertStatus(401);

        $response = $this->post('/api/admin/edit/1');
        $response->assertStatus(401);

        $response = $this->post('/api/admin/delete/1');
        $response->assertStatus(401);
    }
    
    public function testAuthenticatedForCompanyPart()
    {
        $response = $this->post('/api/company/logout');
        $response->assertStatus(401);

        $response = $this->get('/api/company/job/applicant/list');
        $response->assertStatus(401);

        $response = $this->post('/api/company/job/applicant/accept');
        $response->assertStatus(401);

        $response = $this->post('/api/company/job/applicant/recruit');
        $response->assertStatus(401);

        $response = $this->get('/api/company/job/applicant/detail/1');
        $response->assertStatus(401);

        $response = $this->post('/api/company/job/applicant/delete/1');
        $response->assertStatus(401);
        
        $response = $this->get('/api/company/job/chat/detail');
        $response->assertStatus(401);

        $response = $this->post('/api/company/job/chat/delete/1');
        $response->assertStatus(401);

        $response = $this->post('/api/company/edit/1');
        $response->assertStatus(401);

        $response = $this->post('/api/company/report');
        $response->assertStatus(401);

        $response = $this->post('/api/company/job/delete/1');
        $response->assertStatus(401);

        $response = $this->post('/api/company/job/edit/1');
        $response->assertStatus(401);

        $response = $this->post('/api/company/job/add');
        $response->assertStatus(401);

        $response = $this->get('/api/company/job/list');
        $response->assertStatus(401);

        $response = $this->get('/api/company/payment/list');
        $response->assertStatus(401);

        $response = $this->get('/api/company/staff/list');
        $response->assertStatus(401);

        $response = $this->get('/api/company/staff/detail/1');
        $response->assertStatus(401);

        $response = $this->post('/api/company/staff/add');
        $response->assertStatus(401);

        $response = $this->post('/api/company/staff/edit/1');
        $response->assertStatus(401);

        $response = $this->post('/api/company/staff/delete/1');
        $response->assertStatus(401);

        $response = $this->post('/api/company/transfer');
        $response->assertStatus(401);
    }

    public function testAuthenticatedForAppPart()
    {
        $response = $this->post('/api/connector/logout');
        $response->assertStatus(401);

        $response = $this->get('/api/connector/chat/list');
        $response->assertStatus(401);

        $response = $this->get('/api/connector/chat/message-detail/1');
        $response->assertStatus(401);

        $response = $this->post('/api/connector/chat/delete/1');
        $response->assertStatus(401);
        

        $response = $this->get('/api/connector/detail/1');
        $response->assertStatus(401);

        $response = $this->get('/api/connector/total-money');
        $response->assertStatus(401);

        $response = $this->post('/api/connector/report');
        $response->assertStatus(401);

        $response = $this->get('/api/connector/introduction-status');
        $response->assertStatus(401);

        $response = $this->get('/api/connector/job/new');
        $response->assertStatus(401);

        $response = $this->get('/api/connector/job/detail/1');
        $response->assertStatus(401);

        $response = $this->post('/api/connector/job/set-favorite');
        $response->assertStatus(401);

        $response = $this->get('/api/connector/job/favorite');
        $response->assertStatus(401);

        $response = $this->post('/api/connector/job/apply');
        $response->assertStatus(401);

        $response = $this->get('/api/connector/job/list-by-category');
        $response->assertStatus(401);
        
        $response = $this->get('/api/connector/payment/list');
        $response->assertStatus(401);
        
        $response = $this->post('/api/connector/request');
        $response->assertStatus(401);

        $response = $this->post('/api/connector/request/all');
        $response->assertStatus(401);

        $response = $this->get('/api/connector/compensation/history');
        $response->assertStatus(401);
    }
}
