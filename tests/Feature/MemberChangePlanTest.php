<?php

namespace Tests\Feature;

use App\Models\CoachRequest;
use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MemberChangePlanTest extends TestCase
{
    use RefreshDatabase;

    /** Build a member with a running membership, a coach and payment history. */
    private function memberWithHistory(): array
    {
        $user = User::factory()->create(['role' => 'member', 'email' => 'change-plan@test.com']);
        $coach = User::factory()->create(['role' => 'instructor', 'email' => 'change-plan-coach@test.com']);

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
            'receipt_number'  => 'RCP-CHANGE-PLAN-1',
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

    public function test_change_plan_page_shows_only_the_fitness_plan_section(): void
    {
        [$user] = $this->memberWithHistory();

        $response = $this->actingAs($user)->get('/my/select-plan');

        $response->assertOk();
        $response->assertSee('Back to Dashboard');
        $response->assertSee('Choose Your');
        $response->assertSee('Fitness Plan');
        $response->assertSee('Current Plan');
        $response->assertSee('Change Plan');
        foreach (['Calisthenics', 'Bodybuilding', 'Plyometrics', 'Powerlifting', 'Endurance', 'Functional Training', 'Hybrid Training'] as $plan) {
            $response->assertSee($plan);
        }

        $response->assertDontSee('Gym Subscription');
        $response->assertDontSee('Personal Coaching');
        $response->assertDontSee('Coach Subscription Duration');
        $response->assertDontSee('Summary');
        $response->assertDontSee('Total Fee');
        $response->assertDontSee('Complete Subscription');
        $response->assertDontSee('₱');
        $response->assertDontSee('name="membership_type"', false);
        $response->assertDontSee('name="instructor_id"', false);
    }

    public function test_changing_plan_updates_only_the_fitness_plan(): void
    {
        [$user, $member] = $this->memberWithHistory();

        $beforeRow      = $this->memberRowExceptPlan($member->id);
        $paymentsBefore = DB::table('payments')->orderBy('id')->get()->map(fn ($r) => (array) $r)->all();
        $requestsBefore = CoachRequest::count();

        $response = $this->actingAs($user)->post('/my/subscription/update', [
            'fitness_plan' => 'Powerlifting',
        ]);

        $response->assertSessionHas('success');
        $response->assertSessionHasNoErrors();

        // The plan changed...
        $this->assertSame('Powerlifting', $member->fresh()->fitness_plan);

        // ...and nothing else on the member did (dates, type, fee, coach, status).
        $this->assertSame($beforeRow, $this->memberRowExceptPlan($member->id));

        // No payment created or modified, no coach request created.
        $this->assertSame($paymentsBefore, DB::table('payments')->orderBy('id')->get()->map(fn ($r) => (array) $r)->all());
        $this->assertSame($requestsBefore, CoachRequest::count());
    }

    public function test_extra_fields_cannot_change_membership_or_coach(): void
    {
        [$user, $member] = $this->memberWithHistory();
        $otherCoach = User::factory()->create(['role' => 'instructor', 'email' => 'other-coach@test.com']);

        $beforeRow = $this->memberRowExceptPlan($member->id);

        // Simulates a tampered / old-style request that still sends the removed fields.
        $this->actingAs($user)->post('/my/subscription/update', [
            'fitness_plan'          => 'Endurance',
            'membership_type'       => 'Annually',
            'instructor_id'         => $otherCoach->id,
            'coach_membership_type' => 'Annually',
            'end_date'              => now()->addYears(5)->toDateString(),
            'fee'                   => 1,
        ])->assertSessionHasNoErrors();

        $this->assertSame('Endurance', $member->fresh()->fitness_plan);
        $this->assertSame($beforeRow, $this->memberRowExceptPlan($member->id));
    }

    public function test_invalid_fitness_plan_is_rejected(): void
    {
        [$user, $member] = $this->memberWithHistory();

        $this->actingAs($user)->post('/my/subscription/update', [
            'fitness_plan' => 'Not A Real Plan',
        ])->assertSessionHasErrors('fitness_plan');

        $this->assertSame('Calisthenics', $member->fresh()->fitness_plan);
    }

    public function test_user_without_a_membership_sees_notice_and_cannot_create_one(): void
    {
        $user = User::factory()->create(['role' => 'member', 'email' => 'no-membership@test.com']);

        $this->actingAs($user)->get('/my/select-plan')
            ->assertOk()
            ->assertSee("don't have a membership yet", false)
            ->assertDontSee('id="plan-form"', false);

        $this->actingAs($user)->post('/my/subscription/update', ['fitness_plan' => 'Endurance'])
            ->assertSessionHas('error');

        $this->assertSame(0, Member::count());
        $this->assertSame(0, Payment::count());
    }
}