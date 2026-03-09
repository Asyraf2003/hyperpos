<?php

declare(strict_types=1);

namespace Tests\Unit\Adapters\In\Http\Middleware\IdentityAccess;

use App\Adapters\In\Http\Middleware\IdentityAccess\EnsureTransactionEntryAllowed;
use App\Adapters\In\Http\Presenters\Response\JsonResultResponder;
use App\Application\IdentityAccess\Policies\TransactionEntryPolicy;
use App\Core\IdentityAccess\Actor\ActorAccess;
use App\Core\IdentityAccess\Capability\AdminTransactionCapabilityState;
use App\Core\IdentityAccess\Role\Role;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\IdentityAccess\ActorAccessReaderPort;
use App\Ports\Out\IdentityAccess\AdminTransactionCapabilityStatePort;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTransactionEntryAllowedTest extends TestCase
{
    public function test_unauthenticated_request_returns_401(): void
    {
        $middleware = new EnsureTransactionEntryAllowed(
            $this->makePolicy(),
            new JsonResultResponder(),
        );

        $request = Request::create('/transactions', 'POST');

        $response = $middleware->handle(
            $request,
            static fn (): Response => new Response('OK', 200)
        );

        self::assertSame(401, $response->getStatusCode());
        self::assertStringContainsString('UNAUTHENTICATED', (string) $response->getContent());
    }

    public function test_denied_decision_returns_403(): void
    {
        $middleware = new EnsureTransactionEntryAllowed(
            $this->makePolicy(
                actors: [
                    'admin-1' => new ActorAccess('admin-1', Role::admin()),
                ],
                capabilities: [
                    'admin-1' => AdminTransactionCapabilityState::inactive('admin-1'),
                ],
            ),
            new JsonResultResponder(),
        );

        $request = Request::create('/transactions', 'POST');
        $request->setUserResolver(static fn () => new MiddlewareFakeAuthUser('admin-1'));

        $response = $middleware->handle(
            $request,
            static fn (): Response => new Response('OK', 200)
        );

        self::assertSame(403, $response->getStatusCode());
        self::assertStringContainsString('ADMIN_TRANSACTION_CAPABILITY_DISABLED', (string) $response->getContent());
    }

    public function test_allowed_decision_passes_to_next_middleware(): void
    {
        $middleware = new EnsureTransactionEntryAllowed(
            $this->makePolicy(
                actors: [
                    'kasir-1' => new ActorAccess('kasir-1', Role::kasir()),
                ],
            ),
            new JsonResultResponder(),
        );

        $request = Request::create('/transactions', 'POST');
        $request->setUserResolver(static fn () => new MiddlewareFakeAuthUser('kasir-1'));

        $response = $middleware->handle(
            $request,
            static fn (): Response => new Response('OK', 200)
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('OK', $response->getContent());
    }

    /**
     * @param array<string, ActorAccess> $actors
     * @param array<string, AdminTransactionCapabilityState> $capabilities
     */
    private function makePolicy(array $actors = [], array $capabilities = []): TransactionEntryPolicy
    {
        return new TransactionEntryPolicy(
            new MiddlewareActorAccessReaderFake($actors),
            new MiddlewareAdminTransactionCapabilityStateFake($capabilities),
            new MiddlewareAuditLogSpy(),
        );
    }
}

final class MiddlewareActorAccessReaderFake implements ActorAccessReaderPort
{
    /**
     * @param array<string, ActorAccess> $items
     */
    public function __construct(
        private array $items = [],
    ) {
    }

    public function findByActorId(string $actorId): ?ActorAccess
    {
        return $this->items[$actorId] ?? null;
    }
}

final class MiddlewareAdminTransactionCapabilityStateFake implements AdminTransactionCapabilityStatePort
{
    /**
     * @param array<string, AdminTransactionCapabilityState> $items
     */
    public function __construct(
        private array $items = [],
    ) {
    }

    public function getByActorId(string $actorId): AdminTransactionCapabilityState
    {
        return $this->items[$actorId] ?? AdminTransactionCapabilityState::inactive($actorId);
    }

    public function activate(string $actorId): void
    {
        $this->items[$actorId] = AdminTransactionCapabilityState::active($actorId);
    }

    public function deactivate(string $actorId): void
    {
        $this->items[$actorId] = AdminTransactionCapabilityState::inactive($actorId);
    }
}

final class MiddlewareAuditLogSpy implements AuditLogPort
{
    /**
     * @var array<int, array{event:string, context:array<string, mixed>}>
     */
    public array $records = [];

    public function record(string $event, array $context = []): void
    {
        $this->records[] = [
            'event' => $event,
            'context' => $context,
        ];
    }
}

final class MiddlewareFakeAuthUser
{
    public function __construct(
        private readonly string $id,
    ) {
    }

    public function getAuthIdentifier(): string
    {
        return $this->id;
    }
}
