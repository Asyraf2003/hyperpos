<?php

declare(strict_types=1);

namespace Tests\Feature\PushNotification;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PushNotificationAssetFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_layout_exposes_push_assets_and_config(): void
    {
        $this->loginAsAuthorizedAdmin();

        $response = $this->get(route('admin.notes.index'));

        $response->assertOk();
        $response->assertSee('name="csrf-token"', false);
        $response->assertSee('manifest.webmanifest', false);
        $response->assertSee('push-notification-config', false);
        $response->assertSee('service-worker.js', false);
        $response->assertSee('push-notifications.js', false);
        $response->assertSee(route('push-notifications.subscriptions.store'), false);
        $response->assertSee(route('push-notifications.subscriptions.destroy'), false);
    }

    public function test_push_static_assets_exist(): void
    {
        $this->assertFileExists(public_path('manifest.webmanifest'));
        $this->assertFileExists(public_path('service-worker.js'));
        $this->assertFileExists(public_path('assets/static/js/shared/push-notifications.js'));
    }
}
