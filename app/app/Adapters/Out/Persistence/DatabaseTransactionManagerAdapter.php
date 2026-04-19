<?php

declare(strict_types=1);

namespace App\Adapters\Out\Persistence;

use App\Ports\Out\TransactionManagerPort;
use Illuminate\Support\Facades\DB;

final class DatabaseTransactionManagerAdapter implements TransactionManagerPort
{
    public function begin(): void
    {
        DB::beginTransaction();
    }

    public function commit(): void
    {
        DB::commit();
    }

    public function rollBack(): void
    {
        DB::rollBack();
    }
}
