<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiUrlTest extends TestCase
{
    public function testRootUrl()
    {
        $response = $this->get('/api');

        $response->assertStatus(200);
        $response->assertSeeText("Locofull API");
    }

    public function testAPIDocumentUrl()
    {
        $response = $this->get('/api/documentation');

        $response->assertStatus(200);
    }
}
