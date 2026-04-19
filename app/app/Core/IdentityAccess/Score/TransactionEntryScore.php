<?php

declare(strict_types=1);

namespace App\Core\IdentityAccess\Score;

final class TransactionEntryScore
{
    public const KASIR_DEFAULT_ALLOW = 'kasir_default_allow';
    public const ADMIN_CAPABILITY_ALLOW = 'admin_capability_allow';
    public const ADMIN_CAPABILITY_DENY = 'admin_capability_deny';

    private function __construct()
    {
    }
}
