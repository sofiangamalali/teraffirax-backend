<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use App\Models\Creator;
use App\Models\BalanceLedger;
use App\Models\Commission;
use App\Models\Vendor;
use App\Models\Setting;
use App\Mail\DailyDigestMail;
use App\Mail\WeeklyFallbackMail;
use App\Mail\MilestoneAlertMail;
use App\Listeners\LogSentMailNotification;
use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Email;

class CreatorRetentionNotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock Redis in-memory to prevent phpredis driver/connection issues in local environment
        $redisMock = [];
        Redis::shouldReceive('flushall')
            ->zeroOrMoreTimes()
            ->andReturnUsing(function () use (&$redisMock) {
                $redisMock = [];
                return true;
            });
        Redis::shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturnUsing(function ($key) use (&$redisMock) {
                return $redisMock[$key] ?? null;
            });
        Redis::shouldReceive('set')
            ->zeroOrMoreTimes()
            ->andReturnUsing(function ($key, $value, $expireType = null, $expire = null) use (&$redisMock) {
                $redisMock[$key] = $value;
                return true;
            });

        // Register custom SQLite functions for DB queries that use DATEDIFF or NOW (like commission maturation)
        $connection = \Illuminate\Support\Facades\DB::connection();
        if ($connection->getDriverName() === 'sqlite') {
            $pdo = $connection->getPdo();
            $pdo->sqliteCreateFunction('NOW', function () {
                return date('Y-m-d H:i:s');
            });
            $pdo->sqliteCreateFunction('DATEDIFF', function ($date1, $date2) {
                if (!$date1 || !$date2) {
                    return 0;
                }
                $d1 = new \DateTime($date1);
                $d2 = new \DateTime($date2);
                $interval = $d2->diff($d1);
                return (int) $interval->format('%r%a');
            });
        }

        // Set default minimum withdrawal setting
        Setting::updateOrCreate(
            ['key' => 'withdrawal.min_amount'],
            ['value' => '100', 'type' => 'float', 'group' => 'financial']
        );
    }

    public function test_daily_digest_not_sent_on_zero_activity_day()
    {
        Mail::fake();

        // 1. Create active creator with no recent ledger activity
        Creator::factory()->create(['is_active' => true]);

        // 2. Run Daily Digest command
        Artisan::call('notifications:daily-digest');

        // 3. Assert no emails sent
        Mail::assertNothingSent();
    }

    public function test_daily_digest_sent_with_correct_variants()
    {
        Mail::fake();

        // Create 3 creators
        $creatorA = Creator::factory()->create(['is_active' => true, 'available_balance' => 50.00, 'pending_balance' => 10.00]);
        $creatorB = Creator::factory()->create(['is_active' => true, 'available_balance' => 60.00, 'pending_balance' => 20.00]);
        $creatorC = Creator::factory()->create(['is_active' => true, 'available_balance' => 70.00, 'pending_balance' => 30.00]);

        // Creator A: Only commission_matured activity
        \Illuminate\Support\Facades\DB::table('balance_ledgers')->insert([
            'creator_id' => $creatorA->id,
            'type' => 'commission_matured',
            'amount' => 15.00,
            'balance_before' => 35.00,
            'balance_after' => 50.00,
            'created_at' => Carbon::now('Africa/Cairo')->toDateTimeString(),
        ]);

        // Creator B: Only commission_calculated activity
        \Illuminate\Support\Facades\DB::table('balance_ledgers')->insert([
            'creator_id' => $creatorB->id,
            'type' => 'commission_calculated',
            'amount' => 25.00,
            'balance_before' => 35.00,
            'balance_after' => 35.00,
            'created_at' => Carbon::now('Africa/Cairo')->toDateTimeString(),
        ]);

        // Creator C: Both calculation and maturation activity
        \Illuminate\Support\Facades\DB::table('balance_ledgers')->insert([
            'creator_id' => $creatorC->id,
            'type' => 'commission_matured',
            'amount' => 40.00,
            'balance_before' => 30.00,
            'balance_after' => 70.00,
            'created_at' => Carbon::now('Africa/Cairo')->toDateTimeString(),
        ]);
        \Illuminate\Support\Facades\DB::table('balance_ledgers')->insert([
            'creator_id' => $creatorC->id,
            'type' => 'commission_calculated',
            'amount' => 30.00,
            'balance_before' => 70.00,
            'balance_after' => 70.00,
            'created_at' => Carbon::now('Africa/Cairo')->toDateTimeString(),
        ]);

        // Run Daily Digest command
        Artisan::call('notifications:daily-digest');

        // Assert correct variants are queued
        Mail::assertQueued(DailyDigestMail::class, function ($mail) use ($creatorA) {
            return $mail->hasTo($creatorA->email) && $mail->variant === 'A';
        });

        Mail::assertQueued(DailyDigestMail::class, function ($mail) use ($creatorB) {
            return $mail->hasTo($creatorB->email) && $mail->variant === 'B';
        });

        Mail::assertQueued(DailyDigestMail::class, function ($mail) use ($creatorC) {
            return $mail->hasTo($creatorC->email) && $mail->variant === 'C';
        });
    }

    public function test_daily_digest_idempotency()
    {
        Mail::fake();

        $creator = Creator::factory()->create(['is_active' => true]);

        \Illuminate\Support\Facades\DB::table('balance_ledgers')->insert([
            'creator_id' => $creator->id,
            'type' => 'commission_matured',
            'amount' => 50.00,
            'balance_before' => 0,
            'balance_after' => 50.00,
            'created_at' => Carbon::now('Africa/Cairo')->toDateTimeString(),
        ]);

        // Run command first time - should queue
        Artisan::call('notifications:daily-digest');
        Mail::assertQueued(DailyDigestMail::class, 1);

        // Run command second time - should not queue again due to Redis
        Artisan::call('notifications:daily-digest');
        Mail::assertQueued(DailyDigestMail::class, 1); // Still only 1 queued
    }

    public function test_weekly_fallback_activity_guard_and_timing()
    {
        Mail::fake();

        // 1. Creator A: last activity exactly 7 days ago (should receive nudge)
        $creatorA = Creator::factory()->create(['is_active' => true]);
        \Illuminate\Support\Facades\DB::table('balance_ledgers')->insert([
            'creator_id' => $creatorA->id,
            'type' => 'commission_calculated',
            'amount' => 10.00,
            'balance_before' => 0,
            'balance_after' => 0,
            'created_at' => Carbon::now('Africa/Cairo')->subDays(7)->toDateTimeString(),
        ]);

        // 2. Creator B: last activity 6 days ago (should NOT receive nudge)
        $creatorB = Creator::factory()->create(['is_active' => true]);
        \Illuminate\Support\Facades\DB::table('balance_ledgers')->insert([
            'creator_id' => $creatorB->id,
            'type' => 'commission_calculated',
            'amount' => 10.00,
            'balance_before' => 0,
            'balance_after' => 0,
            'created_at' => Carbon::now('Africa/Cairo')->subDays(6)->toDateTimeString(),
        ]);

        // 3. Creator C: last activity 8 days ago (should NOT receive nudge)
        $creatorC = Creator::factory()->create(['is_active' => true]);
        \Illuminate\Support\Facades\DB::table('balance_ledgers')->insert([
            'creator_id' => $creatorC->id,
            'type' => 'commission_calculated',
            'amount' => 10.00,
            'balance_before' => 0,
            'balance_after' => 0,
            'created_at' => Carbon::now('Africa/Cairo')->subDays(8)->toDateTimeString(),
        ]);

        // 4. Creator D: No activity at all (should be skipped due to activity guard)
        $creatorD = Creator::factory()->create(['is_active' => true]);

        // Run weekly fallback
        Artisan::call('notifications:weekly-fallback');

        // Verify fallback queued only to A
        Mail::assertQueued(WeeklyFallbackMail::class, function ($mail) use ($creatorA) {
            return $mail->hasTo($creatorA->email);
        });

        Mail::assertNotQueued(WeeklyFallbackMail::class, function ($mail) use ($creatorB) {
            return $mail->hasTo($creatorB->email);
        });

        Mail::assertNotQueued(WeeklyFallbackMail::class, function ($mail) use ($creatorC) {
            return $mail->hasTo($creatorC->email);
        });

        Mail::assertNotQueued(WeeklyFallbackMail::class, function ($mail) use ($creatorD) {
            return $mail->hasTo($creatorD->email);
        });
    }

    public function test_inactivity_manual_reengagement_flag()
    {
        Mail::fake();

        // Creator A: last activity 31 days ago (should get flagged)
        $creatorA = Creator::factory()->create(['is_active' => true]);
        \Illuminate\Support\Facades\DB::table('balance_ledgers')->insert([
            'creator_id' => $creatorA->id,
            'type' => 'commission_calculated',
            'amount' => 10.00,
            'balance_before' => 0,
            'balance_after' => 0,
            'created_at' => Carbon::now('Africa/Cairo')->subDays(31)->toDateTimeString(),
        ]);

        // Creator B: last activity 20 days ago (should NOT get flagged)
        $creatorB = Creator::factory()->create(['is_active' => true]);
        \Illuminate\Support\Facades\DB::table('balance_ledgers')->insert([
            'creator_id' => $creatorB->id,
            'type' => 'commission_calculated',
            'amount' => 10.00,
            'balance_before' => 0,
            'balance_after' => 0,
            'created_at' => Carbon::now('Africa/Cairo')->subDays(20)->toDateTimeString(),
        ]);

        // Run weekly fallback command containing the checks
        Artisan::call('notifications:weekly-fallback');

        $this->assertTrue((bool) $creatorA->fresh()->needs_manual_reengagement);
        $this->assertFalse((bool) $creatorB->fresh()->needs_manual_reengagement);
    }

    public function test_milestone_alert_triggers_once()
    {
        Mail::fake();

        // 1. Create vendor and creator
        $vendor = Vendor::factory()->create(['commission_payout_days' => 90]);
        $creator = Creator::factory()->create([
            'is_active' => true,
            'available_balance' => 0.00,
            'pending_balance' => 200.00,
            'milestone_threshold_reached_at' => null
        ]);

        // 2. Create unmatured commission earned 95 days ago, amount 120.00 (which will mature and cross the 100 threshold)
        Commission::factory()->create([
            'creator_id' => $creator->id,
            'vendor_id' => $vendor->id,
            'earned_at' => Carbon::now()->subDays(95)->toDateString(),
            'calculated_at' => Carbon::now(),
            'matured_at' => null,
            'commission_amount' => 120.00,
        ]);

        // 3. Run commissions maturation command
        Artisan::call('commissions:mature');

        // 4. Assert that milestone email was queued and milestone timestamp set
        Mail::assertQueued(MilestoneAlertMail::class, function ($mail) use ($creator) {
            return $mail->hasTo($creator->email) && (float) $mail->minimum_withdraw_amount === 100.00 && (float) $mail->current_available === 120.00;
        });

        $this->assertNotNull($creator->fresh()->milestone_threshold_reached_at);

        // 5. Clear mail fake tracking and mature another commission of 50.00
        Mail::fake();
        Commission::factory()->create([
            'creator_id' => $creator->id,
            'vendor_id' => $vendor->id,
            'earned_at' => Carbon::now()->subDays(95)->toDateString(),
            'calculated_at' => Carbon::now(),
            'matured_at' => null,
            'commission_amount' => 50.00,
        ]);

        // Run maturation again
        Artisan::call('commissions:mature');

        // Assert no new MilestoneAlertMail is queued since milestone_threshold_reached_at is already set
        Mail::assertNotQueued(MilestoneAlertMail::class);
    }

    public function test_sent_mail_event_listener_logs_to_database_and_strips_headers()
    {
        // 1. Create a creator
        $creator = Creator::factory()->create();

        // 2. Simulate Symfony Email with custom tracking headers
        $symfonyEmail = new Email();
        $symfonyEmail->to($creator->email);
        $symfonyEmail->from('test@example.com');
        $symfonyEmail->subject('Test Subject');
        $symfonyEmail->text('Test Body');
        $symfonyEmail->getHeaders()->addTextHeader('X-Creator-ID', (string) $creator->id);
        $symfonyEmail->getHeaders()->addTextHeader('X-Email-Type', 'daily_digest');

        // 3. Dispatch Laravel's MessageSending event
        $event = new MessageSending($symfonyEmail);
        
        // Resolve and run the listener
        $listener = app(LogSentMailNotification::class);
        $listener->handle($event);

        // 4. Assert database log entry was created
        $this->assertDatabaseHas('notification_logs', [
            'creator_id' => $creator->id,
            'email_type' => 'daily_digest'
        ]);

        // 5. Assert headers were stripped so they don't leak to recipient
        $this->assertFalse($symfonyEmail->getHeaders()->has('X-Creator-ID'));
        $this->assertFalse($symfonyEmail->getHeaders()->has('X-Email-Type'));
    }
}
