<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Core\Note\Mutation\NoteMutationEvent;
use App\Ports\Out\Note\NoteMutationEventWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabaseNoteMutationEventWriterAdapter implements NoteMutationEventWriterPort
{
    public function create(NoteMutationEvent $event): void
    {
        DB::table('note_mutation_events')->insert([
            'id' => $event->id(),
            'note_id' => $event->noteId(),
            'mutation_type' => $event->mutationType(),
            'actor_id' => $event->actorId(),
            'actor_role' => $event->actorRole(),
            'reason' => $event->reason(),
            'occurred_at' => $event->occurredAt()->format('Y-m-d H:i:s'),
            'related_customer_payment_id' => $event->relatedCustomerPaymentId(),
            'related_customer_refund_id' => $event->relatedCustomerRefundId(),
        ]);
    }
}
