<?php

declare(strict_types=1);

namespace App\Application\IdentityAccess\Request;

use App\Core\IdentityAccess\Actor\ActorAccess;
use App\Core\IdentityAccess\Capability\AdminCashierAreaAccessState;
use App\Core\IdentityAccess\Capability\AdminTransactionCapabilityState;

final class IdentityAccessRequestStore
{
    /**
     * @var array<string, ActorAccess|null>
     */
    private array $actors = [];

    /**
     * @var array<string, AdminCashierAreaAccessState>
     */
    private array $cashierAreaAccess = [];

    /**
     * @var array<string, AdminTransactionCapabilityState>
     */
    private array $transactionCapability = [];

    public function hasActor(string $actorId): bool
    {
        return array_key_exists($actorId, $this->actors);
    }

    public function actor(string $actorId): ?ActorAccess
    {
        return $this->actors[$actorId] ?? null;
    }

    public function rememberActor(string $actorId, ?ActorAccess $actor): ?ActorAccess
    {
        $this->actors[$actorId] = $actor;

        return $actor;
    }

    public function hasCashierAreaAccess(string $actorId): bool
    {
        return array_key_exists($actorId, $this->cashierAreaAccess);
    }

    public function cashierAreaAccess(string $actorId): AdminCashierAreaAccessState
    {
        return $this->cashierAreaAccess[$actorId];
    }

    public function rememberCashierAreaAccess(
        string $actorId,
        AdminCashierAreaAccessState $state
    ): AdminCashierAreaAccessState {
        $this->cashierAreaAccess[$actorId] = $state;

        return $state;
    }

    public function hasTransactionCapability(string $actorId): bool
    {
        return array_key_exists($actorId, $this->transactionCapability);
    }

    public function transactionCapability(string $actorId): AdminTransactionCapabilityState
    {
        return $this->transactionCapability[$actorId];
    }

    public function rememberTransactionCapability(
        string $actorId,
        AdminTransactionCapabilityState $state
    ): AdminTransactionCapabilityState {
        $this->transactionCapability[$actorId] = $state;

        return $state;
    }
}
