<?php

namespace Tests\Feature;

use App\Models\CoachRequest;
use App\Models\Member;
use App\Models\Payment;
use App\Models\PlanChangeRequest;
use App\Models\User;
use App\Notifications\PlanChangeUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MemberChangePlanTest extends TestCase
{
    use RefreshDatabase;

    private const PLANS = ['Calisthenics', 'Bodybuilding', 'Plyometrics', 'Powerlifting', 'Endurance', 'Functional Training', 'Hybrid Training'];

    /** A member with a running membership, an approved coach and payment history. */
    private function memberWithHistory(string $tag = 'a'): array
    {
        $user  = User::factory()->create(['role' => 'member', 'email' => "member-$tag@test.com"]);
        $coach = User::factory()->create(['role' => 'instructor', 'email' => "coach-$tag@test.com"]);

        $member = Member::create([
            'user_id'               => $user->id,
            'name'                  => $user->name,
            'email'                 => $user->email,
            'fitness_plan'          => 'Calisthenics',
            'membership_type'       => 'Quarterly',
            'instructor_id'         => $coach->id,
            'coach_membership_type' => 'Monthly',
            'coach_status'          => 'approved',
            'start_date'            => now()->subDays(10)->toDateString(),
            'end_date'              => now()->addDays(80)->toDateString(),
            'fee'                   => 2100,
            'status'                => 'Active',
        ]);

        Payment::create([
            'member_id'       => $member->id,
            'payment_type'    => 'gym_fee',
            'receipt_number'  => "RCP-CHANGE-PLAN-$tag",
            'amount'          => 2100,
            'fitness_plan'    => 'Calisthenics',
            'membership_type' => 'Quarterly',
            'payment_date'    => now()->subDays(10),
            'status'          => 'Paid',
            'method'          => 'Cash',
            'notes'           => 'Gym membership fee',
        ]);

        return [$user, $member, $coach];
    }

    /** Everything on the member row except the plan and its updated_at stamp. */
    private function memberRowExceptPlan(int $id): array
    {
        $row = (array) DB::table('members')->where('id', $id)->first();
        unset($row['fitness_plan'], $row['updated_at']);

        return $row;
    }

    private function request(User $user, string $plan = 'Bodybuilding', array $extra = [])
    {
        return $this->actingAs($user)->post('/my/subscription/update', ['fitness_plan' => $plan] + $extra);
    }

    // ── Page ────────────────────────────────────────────────────────────────

    public function test_page_keeps_the_seven_plans_and_hides_billing_fields(): void
    {
        [$user] = $this->memberWithHistory();

        $response = $this->actingAs($user)->get('/my/select-plan');

        $response->assertOk()->assertSee('Choose Your')->assertSee('Current Plan')->assertSee('Change Plan');
        foreach (self::PLANS as $plan) {
            $response->assertSee($plan);
        }
        $response->assertDontSee('Total Fee');
        $response->assertDontSee('₱');
        $response->assertDontSee('name="membership_type"', false);
        $response->assertDontSee('name="instructor_id"', false);
    }

    public function test_user_without_a_membership_sees_notice_and_cannot_create_one(): void
    {
        $user = User::factory()->create(['role' => 'member', 'email' => 'no-membership@test.com']);

        $this->actingAs($user)->get('/my/select-plan')
            ->assertOk()
            ->assertSee("don't have a membership yet", false)
            ->assertDontSee('id="plan-form"', false);

        $this->request($user, 'Endurance')->assertSessionHas('error');

        $this->assertSame(0, Member::count());
        $this->assertSame(0, Payment::count());
        $this->assertSame(0, PlanChangeRequest::count());
    }

    // ── Member submits ──────────────────────────────────────────────────────

    public function test_change_plan_creates_a_pending_request_and_changes_nothing_else(): void
    {
        [$user, $member, $coach] = $this->memberWithHistory();

        $rowBefore      = (array) DB::table('members')->where('id', $member->id)->first();
        $paymentsBefore = DB::table('payments')->orderBy('id')->get()->map(fn ($r) => (array) $r)->all();
        $coachReqBefore = CoachRequest::count();

        $this->request($user, 'Bodybuilding', ['reason' => 'Want more muscle'])
            ->assertRedirect(route('member.select-plan'))
            ->assertSessionHas('success', fn ($m) => str_contains($m, 'Bodybuilding') && str_contains($m, 'Calisthenics'));

        $req = PlanChangeRequest::first();
        $this->assertSame('Pending', $req->status);
        $this->assertSame($member->id, $req->member_id);
        $this->assertSame($coach->id, $req->instructor_id);
        $this->assertSame('Calisthenics', $req->current_plan);
        $this->assertSame('Bodybuilding', $req->requested_plan);
        $this->assertSame('Want more muscle', $req->reason);
        $this->assertNotNull($req->requested_at);

        // The real plan, subscription, coach and payments are untouched.
        $this->assertSame($rowBefore, (array) DB::table('members')->where('id', $member->id)->first());
        $this->assertSame($paymentsBefore, DB::table('payments')->orderBy('id')->get()->map(fn ($r) => (array) $r)->all());
        $this->assertSame($coachReqBefore, CoachRequest::count());

        // Member + coach were notified.
        $this->assertSame(1, $user->notifications()->where('type', PlanChangeUpdated::class)->count());
        $this->assertSame(1, $coach->notifications()->where('type', PlanChangeUpdated::class)->count());
    }

    public function test_pending_request_is_shown_separately_and_current_plan_badge_stays(): void
    {
        [$user] = $this->memberWithHistory();
        $this->request($user);

        $this->actingAs($user)->get('/my/select-plan')
            ->assertOk()
            ->assertSee('Pending Coach Approval')
            ->assertSee('Your current plan will remain active until your coach reviews your request.')
            ->assertSee('Request Pending');

        $this->actingAs($user)->get('/my/dashboard')
            ->assertOk()
            ->assertSee('Pending Coach Approval');
    }

    public function test_same_plan_and_invalid_plan_are_rejected(): void
    {
        [$user, $member] = $this->memberWithHistory();

        $this->request($user, 'Calisthenics')->assertSessionHas('error');
        $this->request($user, 'Not A Real Plan')->assertSessionHasErrors('fitness_plan');

        $this->assertSame(0, PlanChangeRequest::count());
        $this->assertSame('Calisthenics', $member->fresh()->fitness_plan);
    }

    public function test_duplicate_pending_request_is_blocked(): void
    {
        [$user] = $this->memberWithHistory();

        $this->request($user, 'Bodybuilding')->assertSessionHas('success');
        $this->request($user, 'Endurance')->assertSessionHas('error');

        $this->assertSame(1, PlanChangeRequest::count());
    }

    public function test_extra_fields_cannot_change_membership_or_coach(): void
    {
        [$user, $member] = $this->memberWithHistory();
        $otherCoach = User::factory()->create(['role' => 'instructor']);
        $before = $this->memberRowExceptPlan($member->id);

        $this->request($user, 'Endurance', [
            'membership_type' => 'Annually', 'instructor_id' => $otherCoach->id,
            'end_date' => now()->addYears(5)->toDateString(), 'fee' => 1,
            'status' => 'Approved', 'instructor' => $otherCoach->id,
        ])->assertSessionHas('success');

        $this->assertSame($before, $this->memberRowExceptPlan($member->id));
        $this->assertSame('Pending', PlanChangeRequest::first()->status);
        $this->assertNotSame($otherCoach->id, PlanChangeRequest::first()->instructor_id);
    }

    public function test_member_without_an_approved_coach_cannot_request(): void
    {
        [$user, $member] = $this->memberWithHistory();
        $member->update(['instructor_id' => null, 'coach_status' => 'none']);

        $this->request($user)->assertSessionHas('error');
        $this->assertSame(0, PlanChangeRequest::count());
    }

    public function test_member_can_cancel_own_pending_request_only(): void
    {
        [$user]           = $this->memberWithHistory('a');
        [$otherUser]      = $this->memberWithHistory('b');

        $this->request($user);
        $req = PlanChangeRequest::first();

        // Another member can't cancel it.
        $this->actingAs($otherUser)->post("/my/plan-change/{$req->id}/cancel")->assertSessionHas('error');
        $this->assertSame('Pending', $req->fresh()->status);

        $this->actingAs($user)->post("/my/plan-change/{$req->id}/cancel")->assertSessionHas('success');
        $this->assertSame('Cancelled', $req->fresh()->status);

        // ...and can submit a new one afterwards.
        $this->request($user, 'Endurance')->assertSessionHas('success');
    }

    // ── Coach reviews ───────────────────────────────────────────────────────

    public function test_coach_sees_request_on_the_existing_requests_page(): void
    {
        [$user, $member, $coach] = $this->memberWithHistory();
        $this->request($user, 'Bodybuilding', ['reason' => 'Bulk season']);

        $this->actingAs($coach)->get('/instructor/requests')
            ->assertOk()
            ->assertSee('Plan Change Requests')
            ->assertSee('Calisthenics')
            ->assertSee('Bodybuilding')
            ->assertSee('Bulk season')
            ->assertSee('Approve Request')
            ->assertSee('Reject Request');
    }

    public function test_approve_updates_only_the_plan_and_notifies_member(): void
    {
        [$user, $member, $coach] = $this->memberWithHistory();
        $this->request($user);
        $req = PlanChangeRequest::first();

        $before         = $this->memberRowExceptPlan($member->id);
        $paymentsBefore = DB::table('payments')->orderBy('id')->get()->map(fn ($r) => (array) $r)->all();

        $this->actingAs($coach)->post("/instructor/plan-changes/{$req->id}/approve")->assertSessionHas('success');

        $req->refresh();
        $this->assertSame('Approved', $req->status);
        $this->assertSame($coach->id, $req->reviewed_by);
        $this->assertNotNull($req->reviewed_at);
        $this->assertNull($req->pending_member_id);

        $this->assertSame('Bodybuilding', $member->fresh()->fitness_plan);
        $this->assertSame($before, $this->memberRowExceptPlan($member->id));
        $this->assertSame($paymentsBefore, DB::table('payments')->orderBy('id')->get()->map(fn ($r) => (array) $r)->all());

        $this->assertTrue($user->notifications->contains(fn ($n) => ($n->data['event'] ?? null) === 'approved'));

        // Member page: current plan moved, nothing pending, approval visible.
        $this->actingAs($user)->get('/my/select-plan')->assertOk()->assertSee('Approved')->assertDontSee('Pending Coach Approval');

        // Allowed to request again after review.
        $this->request($user, 'Endurance')->assertSessionHas('success');
    }

    public function test_reject_requires_reason_keeps_plan_and_shows_feedback(): void
    {
        [$user, $member, $coach] = $this->memberWithHistory();
        $this->request($user);
        $req = PlanChangeRequest::first();

        $this->actingAs($coach)->post("/instructor/plan-changes/{$req->id}/reject", ['rejection_reason' => ''])
            ->assertSessionHas('error');
        $this->assertSame('Pending', $req->fresh()->status);

        $this->actingAs($coach)->post("/instructor/plan-changes/{$req->id}/reject", ['rejection_reason' => 'Finish this block first'])
            ->assertSessionHas('success');

        $req->refresh();
        $this->assertSame('Rejected', $req->status);
        $this->assertSame('Finish this block first', $req->coach_feedback);
        $this->assertSame($coach->id, $req->reviewed_by);
        $this->assertSame('Calisthenics', $member->fresh()->fitness_plan);

        $this->actingAs($user)->get('/my/select-plan')->assertOk()->assertSee('Finish this block first');
    }

    public function test_repeated_processing_is_prevented(): void
    {
        [$user, $member, $coach] = $this->memberWithHistory();
        $this->request($user);
        $req = PlanChangeRequest::first();

        $this->actingAs($coach)->post("/instructor/plan-changes/{$req->id}/approve")->assertSessionHas('success');
        $this->actingAs($coach)->post("/instructor/plan-changes/{$req->id}/approve")->assertSessionHas('error');
        $this->actingAs($coach)->post("/instructor/plan-changes/{$req->id}/reject", ['rejection_reason' => 'Too late now'])->assertSessionHas('error');

        $this->assertSame('Approved', $req->fresh()->status);
        $this->assertSame('Bodybuilding', $member->fresh()->fitness_plan);
    }

    public function test_unauthorised_users_cannot_review(): void
    {
        [$user, $member, $coach] = $this->memberWithHistory('a');
        $otherCoach = User::factory()->create(['role' => 'instructor']);
        $admin      = User::factory()->create(['role' => 'admin']);

        $this->request($user);
        $req = PlanChangeRequest::first();

        // Member approving their own request: blocked by the instructor middleware.
        $this->actingAs($user)->post("/instructor/plan-changes/{$req->id}/approve")->assertRedirect();
        // Another coach / admin: not the assigned coach.
        $this->actingAs($otherCoach)->post("/instructor/plan-changes/{$req->id}/approve")->assertSessionHas('error');
        $this->actingAs($admin)->post("/instructor/plan-changes/{$req->id}/approve")->assertRedirect();

        $this->assertSame('Pending', $req->fresh()->status);
        $this->assertSame('Calisthenics', $member->fresh()->fitness_plan);
    }

    public function test_reassigned_member_request_cannot_be_approved_by_old_coach(): void
    {
        [$user, $member, $coach] = $this->memberWithHistory();
        $newCoach = User::factory()->create(['role' => 'instructor']);

        $this->request($user);
        $req = PlanChangeRequest::first();

        $member->update(['instructor_id' => $newCoach->id]);

        $this->actingAs($coach)->post("/instructor/plan-changes/{$req->id}/approve")->assertSessionHas('error');
        $this->assertSame('Calisthenics', $member->fresh()->fitness_plan);

        // The stale request is closed so the member can ask their new coach.
        $this->request($user, 'Endurance')->assertSessionHas('success');
        $this->assertSame('Cancelled', $req->fresh()->status);
        $this->assertSame($newCoach->id, PlanChangeRequest::where('status', 'Pending')->first()->instructor_id);
    }

    public function test_deleted_coach_does_not_break_pages_or_delete_history(): void
    {
        [$user, $member, $coach] = $this->memberWithHistory();
        $this->request($user);
        $req = PlanChangeRequest::first();

        $member->update(['instructor_id' => null, 'coach_status' => 'none']);
        $coach->delete();

        $this->actingAs($user)->get('/my/select-plan')->assertOk();
        $this->assertSame('Cancelled', $req->fresh()->status);
        $this->assertNull($req->fresh()->instructor_id);
    }
}