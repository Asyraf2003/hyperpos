<?php

declare(strict_types=1);

namespace Tests\Feature\PushNotification;

use Minishlink\WebPush\VAPID;
use Tests\TestCase;

final class WebPushConfigFeatureTest extends TestCase
{
    public function test_web_push_package_is_installed(): void
    {
        $this->assertTrue(class_exists(VAPID::class));
    }

    public function test_web_push_vapid_config_is_available(): void
    {
        config()->set('services.webpush.vapid_subject', 'mailto:test@example.test');
        config()->set('services.webpush.vapid_public_key', 'test-public-key');
        config()->set('services.webpush.vapid_private_key', 'test-private-key');

        $this->assertSame('mailto:test@example.test', config('services.webpush.vapid_subject'));
        $this->assertSame('test-public-key', config('services.webpush.vapid_public_key'));
        $this->assertSame('test-private-key', config('services.webpush.vapid_private_key'));
    }
}
