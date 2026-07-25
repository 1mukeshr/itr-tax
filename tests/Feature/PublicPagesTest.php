<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_and_tools_pages_load(): void
    {
        $this->seed();

        $this->get('/')->assertOk();
        $this->get('/tools')->assertOk();
        $this->get('/tools/hra-calculator')->assertOk();
        $this->get('/tools/rent-receipt')->assertOk();
        $this->get('/forgot-password')->assertOk();
        $this->get('/login')->assertOk();
    }

    public function test_hra_compute_returns_result(): void
    {
        $this->seed();

        $this->post('/tools/hra-calculator', [
            'basic' => 600000,
            'hra_received' => 240000,
            'rent_paid' => 300000,
            'metro' => '0',
        ])->assertOk()->assertSee('Exempt HRA');
    }
}
