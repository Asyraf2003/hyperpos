<?php

declare(strict_types=1);

namespace App\Application\IdentityAccess\Request;

use App\Core\IdentityAccess\Actor\ActorAccess;
use App\Core\IdentityAccess\Capability\AdminCashierAreaAccessState;
use App\Core\IdentityAccess\Capability\AdminTransactionCapabilityState;
use App\Ports\Out\IdentityAccess\ActorAccessReaderPort;
use App\Ports\Out\IdentityAccess\AdminCashierAreaAccessStatePort;
use App\Ports\Out\IdentityAccess\AdminTransactionCapabilityStatePort;

final class IdentityAccessRequestCache
{
    /**
     * @var array<string, ActorAccess|null>
     */
    private array $actors = [];

    /**
     * @var array<string, AdminCashierAreaAccessState>
     */
    private array $cashierAreaCapabilities = [];

    /**
     * @var array<string, AdminTransactionCapabilityState>
     */
    private array $transactionCapabilities = [];

    public function __construct(
        private readonly ActorAccessReaderPort $actorsPort,
        private readonly AdminCashierAreaAccessStatePort $cashierAreaPort,
        private readonly AdminTransactionCapabilityStatePort $transactionPort,
    ) {
    }

    public function actorById(string $actorId): ?ActorAccess
    {
        if (array_key_exists($actorId, $this->actors)) {
            return $this->actors[$actorId];
        }

        $this->actors[$actorId] = $this->actorsPort->findByActorId($actorId);

        return $this->actors[$actorId];
    }

    public function adminCashierAreaAccessByActorId(string $actorId): AdminCashierAreaAccessState
    {
        if (array_key_exists($actorId, $this->cashierAreaCapabilities)) {
            return $this->cashierAreaCapabilities[$actorId];
        }

        $this->cashierAreaCapabilities[$actorId] = $this->cashierAreaPort->getByActorId($actorId);

        return $this->cashierAreaCapabilities[$actorId];
    }

    public function adminTransactionCapabilityByActorId(string $actorId): AdminTransactionCapabilityState
    {
        if (array_key_exists($actorId, $this->transactionCapabilities)) {
            return $this->transactionCapabilities[$actorId];
        }

        $this->transactionCapabilities[$actorId] = $this->transactionPort->getByActorId($actorId);

        return $this->transactionCapabilities[$actorId];
    }
}
