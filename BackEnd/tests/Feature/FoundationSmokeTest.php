<?php

namespace Tests\Feature;

use Tests\TestCase;

class FoundationSmokeTest extends TestCase
{
    public function test_welcome_page_is_available(): void
    {
        $this->get('/')
            ->assertOk();
    }

    public function test_health_endpoint_reports_up_without_exposing_details(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertExactJson(['status' => 'up']);
    }
}
