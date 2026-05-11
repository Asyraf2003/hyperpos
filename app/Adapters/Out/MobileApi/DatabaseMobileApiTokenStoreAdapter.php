<?php

declare(strict_types=1);

namespace App\Adapters\Out\MobileApi;

use App\Application\MobileApi\Auth\DTO\MobileApiTokenRecord;
use App\Ports\Out\MobileApi\MobileApiTokenStorePort;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class DatabaseMobileApiTokenStoreAdapter implements MobileApiTokenStorePort
{
    public function create(
        string $userId,
        string $tokenHash,
        string $deviceName,
        CarbonImmutable $expiresAt,
        CarbonImmutable $now,
    ): MobileApiTokenRecord {
        $id = DB::table('mobile_api_tokens')->insertGetId([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'device_name' => $deviceName,
            'expires_at' => $expiresAt->toDateTimeString(),
            'created_at' => $now->toDateTimeString(),
            'updated_at' => $now->toDateTimeString(),
        ]);

        return new MobileApiTokenRecord(
            id: (string) $id,
            userId: $userId,
            deviceName: $deviceName,
            expiresAt: $expiresAt,
        );
    }

    public function findActiveByTokenHash(
        string $tokenHash,
        CarbonImmutable $now,
    ): ?MobileApiTokenRecord {
        $row = DB::table('mobile_api_tokens')
            ->where('token_hash', $tokenHash)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', $now->toDateTimeString())
            ->first();

        if ($row === null) {
            return null;
        }

        DB::table('mobile_api_tokens')
            ->where('id', $row->id)
            ->update([
                'last_used_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ]);

        return new MobileApiTokenRecord(
            id: (string) $row->id,
            userId: (string) $row->user_id,
            deviceName: (string) $row->device_name,
            expiresAt: CarbonImmutable::parse((string) $row->expires_at),
        );
    }

    public function revokeById(string $tokenId, CarbonImmutable $now): void
    {
        DB::table('mobile_api_tokens')
            ->where('id', $tokenId)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ]);
    }
}
