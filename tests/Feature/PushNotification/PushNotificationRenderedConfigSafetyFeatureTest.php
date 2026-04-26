<?php

declare(strict_types=1);

namespace Tests\Feature\PushNotification;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PushNotificationRenderedConfigSafetyFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_layout_exposes_public_vapid_key_but_not_private_key(): void
    {
        config()->set('services.webpush.vapid_public_key', 'public-key-visible-to-browser');
        config()->set('services.webpush.vapid_private_key', 'private-key-must-not-leak');

        $this->loginAsAuthorizedAdmin();

        $response = $this->get(route('admin.due-note-reminders.index'));

        $response->assertOk();
        $response->assertSee('push-notification-config', false);
        $response->assertSee('public-key-visible-to-browser', false);
        $response->assertDontSee('private-key-must-not-leak', false);
    }
}
