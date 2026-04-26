<?php

declare(strict_types=1);

namespace Tests\Feature\PushNotification;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PushSubscriptionEndpointFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_store_push_subscription(): void
    {
        $user = $this->loginAsAuthorizedAdmin();

        $response = $this->postJson(route('push-notifications.subscriptions.store'), [
            'endpoint' => 'https://push.example.test/send/browser-1',
            'keys' => [
                'p256dh' => 'public-key-browser-1',
                'auth' => 'auth-token-browser-1',
            ],
            'contentEncoding' => 'aes128gcm',
        ]);

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'stored' => true,
            ],
        ]);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->getAuthIdentifier(),
            'endpoint_hash' => hash('sha256', 'https://push.example.test/send/browser-1'),
            'endpoint' => 'https://push.example.test/send/browser-1',
            'public_key' => 'public-key-browser-1',
            'auth_token' => 'auth-token-browser-1',
            'content_encoding' => 'aes128gcm',
        ]);
    }

    public function test_authenticated_user_can_delete_own_push_subscription(): void
    {
        $this->loginAsAuthorizedAdmin();

        $endpoint = 'https://push.example.test/send/browser-delete';

        $this->postJson(route('push-notifications.subscriptions.store'), [
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => 'public-key-delete',
                'auth' => 'auth-token-delete',
            ],
            'contentEncoding' => 'aes128gcm',
        ])->assertOk();

        $response = $this->deleteJson(route('push-notifications.subscriptions.destroy'), [
            'endpoint' => $endpoint,
        ]);

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'deleted' => true,
            ],
        ]);

        $this->assertDatabaseMissing('push_subscriptions', [
            'endpoint_hash' => hash('sha256', $endpoint),
        ]);
    }

    public function test_store_subscription_rejects_invalid_payload(): void
    {
        $this->loginAsAuthorizedAdmin();

        $response = $this->postJson(route('push-notifications.subscriptions.store'), [
            'endpoint' => 'not-a-url',
            'keys' => [
                'p256dh' => '',
                'auth' => '',
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'endpoint',
            'keys.p256dh',
            'keys.auth',
        ]);
    }
}
