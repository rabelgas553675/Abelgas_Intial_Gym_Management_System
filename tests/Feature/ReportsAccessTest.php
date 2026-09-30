<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_reports()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin-report@test.com',
        ]);

        $response = $this->actingAs($user)->get('/reports');

        $response->assertOk();
        $response->assertViewHas('allowedTypes', ['payment', 'attendance', 'workout']);
    }

    public function test_staff_can_only_view_staff_allowed_reports()
    {
        $user = User::factory()->create([
            'role' => 'staff',
            'email' => 'staff-report@test.com',
        ]);

        $response = $this->actingAs($user)->get('/reports');

        $response->assertOk();
        $response->assertViewHas('allowedTypes', ['payment', 'attendance']);
    }

    public function test_instructor_can_only_view_instructor_allowed_reports()
    {
        $user = User::factory()->create([
            'role' => 'instructor',
            'email' => 'instructor-report@test.com',
        ]);

        $response = $this->actingAs($user)->get('/reports');

        $response->assertOk();
        $response->assertViewHas('allowedTypes', ['attendance', 'workout']);
    }

    public function test_admin_can_export_excel_report()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin-export@test.com',
        ]);

        $response = $this->actingAs($user)->get('/reports/export?type=payment&range=month&format=excel');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');
    }

    public function test_subscription_rates_are_available_for_members_and_instructors()
    {
        $this->assertSame(800, \App\Models\Payment::gymRate('Monthly'));
        $this->assertSame(300, \App\Models\Payment::coachRate('Monthly'));
        $this->assertSame(2100, \App\Models\Payment::gymRate('Quarterly'));
        $this->assertSame(1200, \App\Models\Payment::coachRate('Quarterly'));
    }

    public function test_report_filter_accepts_calendar_date_selection()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin-calendar-report@test.com',
        ]);

        $response = $this->actingAs($user)->get('/reports?type=payment&range=day&date=2026-04-14');

        $response->assertOk();
        $response->assertViewHas('selectedDate', '2026-04-14');
    }

    public function test_updated_rate_settings_are_used_for_member_pricing()
    {
        \App\Models\PaymentSetting::query()->delete();

        \App\Models\PaymentSetting::updateOrCreate(
            ['key' => 'gym_Monthly'],
            ['value' => '1500']
        );
        \App\Models\PaymentSetting::updateOrCreate(
            ['key' => 'coach_Monthly'],
            ['value' => '500']
        );

        $this->assertSame(1500, \App\Models\Payment::gymRate('Monthly'));
        $this->assertSame(500, \App\Models\Payment::coachRate('Monthly'));
        $this->assertSame(1500, \App\Models\Payment::gymRates()['Monthly']);
        $this->assertSame(500, \App\Models\Payment::coachRates()['Monthly']);
    }
}
