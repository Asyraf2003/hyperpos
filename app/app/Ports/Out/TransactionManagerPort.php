<?php

declare(strict_types=1);

namespace App\Ports\Out;

interface TransactionManagerPort
{
    public function begin(): void;

    public function commit(): void;

    public function rollBack(): void;
}
