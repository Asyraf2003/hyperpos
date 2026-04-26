<?php

declare(strict_types=1);

namespace Tests\Feature\PushNotification;

use App\Application\PushNotification\DTO\PushNotificationPayload;
use App\Application\PushNotification\DTO\StoredPushSubscription;
use App\Ports\Out\PushNotification\PushNotificationSenderPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SendDueNoteReminderPushCommandFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_sends_due_note_reminder_payload_to_active_subscriptions(): void
    {
        $user = $this->loginAsAuthorizedAdmin();
        $sender = new RecordingPushNotificationSender();
        $this->app->instance(PushNotificationSenderPort::class, $sender);

        $this->seedDueReminderNote();
        $this->seedPushSubscription((int) $user->getAuthIdentifier(), 'browser-1');
        $this->seedPushSubscription((int) $user->getAuthIdentifier(), 'browser-2');

        $this->artisan('push-notifications:send-due-note-reminders', [
            '--today' => '2026-04-25',
        ])
            ->expectsOutput('Due reminder notes: 1')
            ->expectsOutput('Push subscriptions: 2')
            ->expectsOutput('Push sent: 2')
            ->expectsOutput('Push failed: 0')
            ->assertExitCode(0);

        $this->assertCount(2, $sender->payloads);
        $this->assertSame('Reminder Jatuh Tempo Nota', $sender->payloads[0]->title);
        $this->assertStringContainsString('Ada 1 nota jatuh tempo/perlu dicek.', $sender->payloads[0]->body);
        $this->assertStringContainsString('Rp 150.000', $sender->payloads[0]->body);
        $this->assertSame('/admin/due-note-reminders?today=2026-04-25', $sender->payloads[0]->url);
        $this->assertSame('due-note-reminder-2026-04-25', $sender->payloads[0]->tag);
    }

    public function test_command_skips_push_when_no_due_reminder_note_exists(): void
    {
        $sender = new RecordingPushNotificationSender();
        $this->app->instance(PushNotificationSenderPort::class, $sender);

        $this->artisan('push-notifications:send-due-note-reminders', [
            '--today' => '2026-04-25',
        ])
            ->expectsOutput('Due reminder notes: 0')
            ->expectsOutput('Push subscriptions: 0')
            ->expectsOutput('Push sent: 0')
            ->expectsOutput('Push failed: 0')
            ->assertExitCode(0);

        $this->assertSame([], $sender->payloads);
    }

    private function seedDueReminderNote(): void
    {
        DB::table('notes')->insert([
            'id' => 'note-push-due-1',
            'customer_name' => 'Budi Push',
            'customer_phone' => '081234567890',
            'transaction_date' => '2026-03-30',
            'due_date' => '2026-04-30',
            'total_rupiah' => 200000,
            'note_state' => 'open',
        ]);

        DB::table('note_history_projection')->insert([
            'note_id' => 'note-push-due-1',
            'transaction_date' => '2026-03-30',
            'note_state' => 'open',
            'customer_name' => 'Budi Push',
            'customer_name_normalized' => 'budi push',
            'customer_phone' => '081234567890',
            'total_rupiah' => 200000,
            'allocated_rupiah' => 50000,
            'refunded_rupiah' => 0,
            'net_paid_rupiah' => 50000,
            'outstanding_rupiah' => 150000,
            'line_open_count' => 1,
            'line_close_count' => 0,
            'line_refund_count' => 0,
            'has_open_lines' => true,
            'has_close_lines' => false,
            'has_refund_lines' => false,
            'projected_at' => '2026-04-25 10:00:00',
        ]);
    }

    private function seedPushSubscription(int $userId, string $browser): void
    {
        $endpoint = 'https://push.example.test/send/'.$browser;

        DB::table('push_subscriptions')->insert([
            'user_id' => $userId,
            'endpoint' => $endpoint,
            'endpoint_hash' => hash('sha256', $endpoint),
            'public_key' => 'public-key-'.$browser,
            'auth_token' => 'auth-token-'.$browser,
            'content_encoding' => 'aes128gcm',
            'user_agent' => 'Feature Test '.$browser,
            'last_seen_at' => '2026-04-25 10:00:00',
            'created_at' => '2026-04-25 10:00:00',
            'updated_at' => '2026-04-25 10:00:00',
        ]);
    }
}

final class RecordingPushNotificationSender implements PushNotificationSenderPort
{
    /** @var list<StoredPushSubscription> */
    public array $subscriptions = [];

    /** @var list<PushNotificationPayload> */
    public array $payloads = [];

    public function send(StoredPushSubscription $subscription, PushNotificationPayload $payload): bool
    {
        $this->subscriptions[] = $subscription;
        $this->payloads[] = $payload;

        return true;
    }
}
