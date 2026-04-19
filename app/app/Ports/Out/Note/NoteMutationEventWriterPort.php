<?php

declare(strict_types=1);

namespace App\Ports\Out\Note;

use App\Core\Note\Mutation\NoteMutationEvent;

interface NoteMutationEventWriterPort
{
    public function create(NoteMutationEvent $event): void;
}
