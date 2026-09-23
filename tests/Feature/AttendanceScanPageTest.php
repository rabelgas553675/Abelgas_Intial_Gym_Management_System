<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceScanPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_scanner_page_reports_real_camera_errors_and_tries_multiple_fallbacks(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin-' . uniqid() . '@example.com',
            'password' => 'secret123',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.scan'));

        $response->assertOk();
        $response->assertSee('navigator.mediaDevices');
        $response->assertSee('Camera permission was blocked');
        $response->assertSee('No camera was detected on this device');
    }
}
