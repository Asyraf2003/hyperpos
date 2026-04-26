<?php

declare(strict_types=1);

namespace Tests\Feature\PushNotification;

use App\Application\PushNotification\DTO\PushSubscriptionData;
use App\Application\PushNotification\UseCases\StorePushSubscriptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StorePushSubscriptionHandlerFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_push_subscription_for_authenticated_user(): void
    {
        $user = $this->loginAsAuthorizedAdmin();

        app(StorePushSubscriptionHandler::class)->handle(new PushSubscriptionData(
            userId: (int) $user->getAuthIdentifier(),
            endpoint: 'https://push.example.test/send/one',
            publicKey: 'public-key-one',
            authToken: 'auth-token-one',
            contentEncoding: 'aes128gcm',
            userAgent: 'Feature Test Browser',
        ));

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->getAuthIdentifier(),
            'endpoint_hash' => hash('sha256', 'https://push.example.test/send/one'),
            'endpoint' => 'https://push.example.test/send/one',
            'public_key' => 'public-key-one',
            'auth_token' => 'auth-token-one',
            'content_encoding' => 'aes128gcm',
            'user_agent' => 'Feature Test Browser',
        ]);
    }

    public function test_it_updates_existing_subscription_by_endpoint_hash(): void
    {
        $firstUser = $this->loginAsAuthorizedAdmin();

        app(StorePushSubscriptionHandler::class)->handle(new PushSubscriptionData(
            userId: (int) $firstUser->getAuthIdentifier(),
            endpoint: 'https://push.example.test/send/reused',
            publicKey: 'public-key-old',
            authToken: 'auth-token-old',
            contentEncoding: 'aes128gcm',
            userAgent: 'Old Browser',
        ));

        $secondUser = $this->loginAsAuthorizedAdmin();

        app(StorePushSubscriptionHandler::class)->handle(new PushSubscriptionData(
            userId: (int) $secondUser->getAuthIdentifier(),
            endpoint: 'https://push.example.test/send/reused',
            publicKey: 'public-key-new',
            authToken: 'auth-token-new',
            contentEncoding: 'aes128gcm',
            userAgent: 'New Browser',
        ));

        $this->assertDatabaseCount('push_subscriptions', 1);
        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $secondUser->getAuthIdentifier(),
            'endpoint_hash' => hash('sha256', 'https://push.example.test/send/reused'),
            'public_key' => 'public-key-new',
            'auth_token' => 'auth-token-new',
            'user_agent' => 'New Browser',
        ]);
    }
}
