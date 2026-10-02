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
        $response->assertViewHas('allowedTypes', ['payment', 'attendance', 'workout', 'member']);
    }

    public function test_staff_can_only_view_staff_allowed_reports()
    {
        $user = User::factory()->create([
            'role' => 'staff',
            'email' => 'staff-report@test.com',
        ]);

        $response = $this->actingAs($user)->get('/reports');

        $response->assertOk();
        $response->assertViewHas('allowedTypes', ['payment', 'attendance', 'member']);
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

    public function test_admin_can_export_member_report_as_pdf()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin-member-pdf@test.com',
        ]);

        \App\Models\Member::create([
            'user_id' => User::factory()->create(['role' => 'member', 'email' => 'member-pdf@test.com'])->id,
            'name' => 'PDF Member',
            'email' => 'pdfmember@test.com',
            'status' => 'Active',
            'fitness_plan' => 'Hybrid Training',
            'membership_type' => 'Monthly',
            'created_at' => '2026-09-15 10:00:00',
            'updated_at' => '2026-09-15 10:00:00',
        ]);

        $response = $this->actingAs($user)->get('/reports/export?type=member&range=month&format=pdf');

        $response->assertOk();
        $response->assertSee('Member report');
        $response->assertSee('PDF Member');
        $response->assertSee('Analytics');
    }

    public function test_report_includes_analytics_chart_data()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin-chart@test.com',
        ]);

        $response = $this->actingAs($user)->get('/reports?type=payment&range=month&date=2026-09');

        $response->assertOk();
        $response->assertViewHas('report', function ($report) {
            return isset($report['chart']) && is_array($report['chart']) && ! empty($report['chart']);
        });
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

    public function test_admin_can_update_instructor_specific_subscription_rate()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin-instructor-rate@test.com',
        ]);

        $instructor = User::factory()->create([
            'role' => 'instructor',
            'email' => 'instructor-rate@test.com',
        ]);

        $response = $this->actingAs($admin)->from('/reports')->post('/payments/settings', [
            'gym_monthly' => 800,
            'gym_quarterly' => 2100,
            'gym_semi_annual' => 4500,
            'gym_annually' => 7500,
            'coach_monthly' => 450,
            'coach_quarterly' => 1300,
            'coach_semi_annual' => 2200,
            'coach_annually' => 4200,
            'instructor_id' => $instructor->id,
        ]);

        $response->assertRedirect();
        $this->assertSame(450, \App\Models\Payment::coachRate('Monthly', $instructor->id));
        $this->assertSame(300, \App\Models\Payment::coachRate('Monthly'));
        $this->assertDatabaseHas('payment_settings', [
            'key' => 'coach_instructor_' . $instructor->id . '_Monthly',
            'value' => '450',
        ]);
    }

    public function test_instructor_can_see_current_rate_and_rate_change_notification()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin-rate-notify@test.com',
        ]);

        $instructor = User::factory()->create([
            'role' => 'instructor',
            'email' => 'instructor-rate-notify@test.com',
        ]);

        $this->actingAs($admin)->post('/payments/settings', [
            'gym_monthly' => 800,
            'gym_quarterly' => 2100,
            'gym_semi_annual' => 4500,
            'gym_annually' => 7500,
            'coach_monthly' => 450,
            'coach_quarterly' => 1300,
            'coach_semi_annual' => 2200,
            'coach_annually' => 4200,
            'instructor_id' => $instructor->id,
        ]);

        $instructor->refresh();
        $this->assertNotEmpty($instructor->notifications);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $instructor->id,
            'notifiable_type' => User::class,
        ]);

        $response = $this->actingAs($instructor)->get('/instructor/dashboard');
        $response->assertOk();
        $response->assertSee('My Coaching Rate');
        $response->assertSee('Monthly');
        $response->assertSee('₱450');
    }

    public function test_admin_rate_override_persists_for_selected_instructor_after_refresh()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin-rate-persist@test.com',
        ]);

        $instructor = User::factory()->create([
            'role' => 'instructor',
            'email' => 'instructor-rate-persist@test.com',
        ]);

        $response = $this->actingAs($admin)->post('/payments/settings', [
            'gym_monthly' => 800,
            'gym_quarterly' => 2100,
            'gym_semi_annual' => 4500,
            'gym_annually' => 7500,
            'coach_monthly' => 4600,
            'coach_quarterly' => 1300,
            'coach_semi_annual' => 2200,
            'coach_annually' => 4200,
            'instructor_id' => $instructor->id,
        ]);

        $response->assertRedirect('/payments?instructor_id=' . $instructor->id);

        $page = $this->actingAs($admin)->get('/payments?instructor_id=' . $instructor->id);
        $page->assertOk();
        $page->assertSee('4600');
        $page->assertSee($instructor->name);
    }

    public function test_member_select_plan_shows_selected_instructor_override_rate()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin-member-rate@test.com',
        ]);

        $instructor = User::factory()->create([
            'role' => 'instructor',
            'email' => 'instructor-member-rate@test.com',
        ]);

        $memberUser = User::factory()->create([
            'role' => 'member',
            'email' => 'member-rate-view@test.com',
        ]);

        $memberProfile = \App\Models\Member::create([
            'user_id' => $memberUser->id,
            'name' => $memberUser->name,
            'email' => $memberUser->email,
            'instructor_id' => $instructor->id,
            'status' => 'Active',
        ]);

        $this->actingAs($admin)->post('/payments/settings', [
            'gym_monthly' => 800,
            'gym_quarterly' => 2100,
            'gym_semi_annual' => 4500,
            'gym_annually' => 7500,
            'coach_monthly' => 4600,
            'coach_quarterly' => 1300,
            'coach_semi_annual' => 2200,
            'coach_annually' => 4200,
            'instructor_id' => $instructor->id,
        ]);

        $response = $this->actingAs($memberUser)->get('/my/select-plan');
        $response->assertOk();
        $response->assertSee('₱4,600');
    }

    public function test_member_subscribe_redirects_home_instead_of_waiting_page()
    {
        $member = User::factory()->create([
            'role' => 'member',
            'email' => 'member-subscribe-home@test.com',
        ]);

        $instructor = User::factory()->create([
            'role' => 'instructor',
            'email' => 'instructor-subscribe-home@test.com',
        ]);

        $response = $this->actingAs($member)->post('/my/subscribe', [
            'fitness_plan' => 'Calisthenics',
            'membership_type' => 'Monthly',
            'instructor_id' => $instructor->id,
            'coach_membership_type' => 'Monthly',
        ]);

        $response->assertRedirect('/my/dashboard');
    }

    public function test_staff_can_record_full_subscription_payment_with_optional_coaching()
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email' => 'staff-payment@test.com',
        ]);

        $member = \App\Models\Member::create([
            'user_id' => User::factory()->create(['role' => 'member', 'email' => 'member-payment@test.com'])->id,
            'name' => 'Jane Member',
            'email' => 'janemember@test.com',
            'membership_type' => 'Monthly',
            'fitness_plan' => 'Calisthenics',
            'status' => 'Active',
        ]);

        $instructor = User::factory()->create([
            'role' => 'instructor',
            'email' => 'instructor-payment@test.com',
        ]);

        $response = $this->actingAs($staff)->post('/payments', [
            'member_id' => $member->id,
            'fitness_plan' => 'Hybrid Training',
            'membership_type' => 'Quarterly',
            'instructor_id' => $instructor->id,
            'coach_membership_type' => 'Monthly',
            'payment_date' => '2026-09-30',
            'method' => 'GCash',
            'notes' => 'Quarterly gym + monthly coaching package',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'member_id' => $member->id,
            'payment_type' => 'gym_fee',
            'fitness_plan' => 'Hybrid Training',
            'membership_type' => 'Quarterly',
        ]);
        $this->assertDatabaseHas('payments', [
            'member_id' => $member->id,
            'payment_type' => 'coach_fee',
            'instructor_id' => $instructor->id,
            'membership_type' => 'Monthly',
        ]);

        $member->refresh();
        $this->assertSame('Hybrid Training', $member->fitness_plan);
        $this->assertSame('Quarterly', $member->membership_type);
        $this->assertSame($instructor->id, $member->instructor_id);
        $this->assertSame('Monthly', $member->coach_membership_type);
    }

    public function test_member_can_view_own_attendance_history()
    {
        $user = User::factory()->create([
            'role' => 'member',
            'email' => 'member-attendance-history@test.com',
        ]);

        $member = \App\Models\Member::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => 'Active',
            'fitness_plan' => 'Hybrid Training',
            'membership_type' => 'Monthly',
        ]);

        \App\Models\Attendance::create([
            'member_id' => $member->id,
            'staff_user_id' => $user->id,
            'date' => '2026-10-01',
            'time_in' => '2026-10-01 08:00:00',
            'time_out' => '2026-10-01 09:30:00',
            'duration_minutes' => 90,
            'entry_method' => 'qr',
            'scanned_by' => 'member',
        ]);

        $response = $this->actingAs($user)->get('/my/attendance-history');

        $response->assertOk();
        $response->assertSee('Attendance History');
        $response->assertSee('Hybrid Training');
    }

    public function test_member_can_view_own_subscription_history()
    {
        $user = User::factory()->create([
            'role' => 'member',
            'email' => 'member-subscription-history@test.com',
        ]);

        $member = \App\Models\Member::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => 'Active',
            'fitness_plan' => 'Powerlifting',
            'membership_type' => 'Quarterly',
        ]);

        \App\Models\Payment::create([
            'member_id' => $member->id,
            'payment_type' => 'gym_fee',
            'receipt_number' => 'RCP-SUB-HISTORY-1',
            'amount' => 2100,
            'fitness_plan' => 'Powerlifting',
            'membership_type' => 'Quarterly',
            'payment_date' => '2026-10-01',
            'status' => 'Paid',
            'method' => 'Cash',
            'notes' => 'Quarterly membership',
        ]);

        $response = $this->actingAs($user)->get('/my/subscription-history');

        $response->assertOk();
        $response->assertSee('Subscription History');
        $response->assertSee('Powerlifting');
        $response->assertSee('Quarterly');
    }

    public function test_member_renewal_adds_to_existing_subscription_end_date()
    {
        $user = User::factory()->create([
            'role' => 'member',
            'email' => 'member-renewal-accumulates@test.com',
        ]);

        $member = \App\Models\Member::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'fitness_plan' => 'Calisthenics',
            'membership_type' => 'Monthly',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-30',
            'status' => 'Active',
            'fee' => 800,
            'coach_status' => 'none',
        ]);

        $response = $this->actingAs($user)->post('/my/subscribe', [
            'fitness_plan' => 'Calisthenics',
            'membership_type' => 'Monthly',
        ]);

        $response->assertRedirect();

        $member->refresh();
        $this->assertSame('2026-11-30', $member->end_date->format('Y-m-d'));
        $this->assertDatabaseHas('payments', [
            'member_id' => $member->id,
            'payment_type' => 'gym_fee',
            'membership_type' => 'Monthly',
        ]);
    }
}
