<?php

declare(strict_types=1);

namespace App\Adapters\Out\PushNotification;

use App\Application\PushNotification\DTO\PushSubscriptionData;
use App\Ports\Out\PushNotification\PushSubscriptionWriterPort;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class DatabasePushSubscriptionWriterAdapter implements PushSubscriptionWriterPort
{
    public function upsert(PushSubscriptionData $subscription): void
    {
        $endpoint = $this->required($subscription->endpoint, 'Endpoint push wajib diisi.');
        $publicKey = $this->required($subscription->publicKey, 'Public key push wajib diisi.');
        $authToken = $this->required($subscription->authToken, 'Auth token push wajib diisi.');
        $contentEncoding = $this->required($subscription->contentEncoding, 'Content encoding push wajib diisi.');
        $now = now()->format('Y-m-d H:i:s');

        DB::table('push_subscriptions')->updateOrInsert(
            ['endpoint_hash' => hash('sha256', $endpoint)],
            [
                'user_id' => $subscription->userId,
                'endpoint' => $endpoint,
                'public_key' => $publicKey,
                'auth_token' => $authToken,
                'content_encoding' => $contentEncoding,
                'user_agent' => $subscription->userAgent,
                'last_seen_at' => $now,
                'updated_at' => $now,
                'created_at' => DB::raw('COALESCE(created_at, '.$this->quotedNow($now).')'),
            ],
        );
    }

    public function deleteForUserEndpoint(int $userId, string $endpoint): void
    {
        $normalizedEndpoint = $this->required($endpoint, 'Endpoint push wajib diisi.');

        DB::table('push_subscriptions')
            ->where('user_id', $userId)
            ->where('endpoint_hash', hash('sha256', $normalizedEndpoint))
            ->delete();
    }

    private function required(string $value, string $message): string
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new InvalidArgumentException($message);
        }

        return $trimmed;
    }

    private function quotedNow(string $now): string
    {
        return DB::getPdo()->quote($now);
    }
}
