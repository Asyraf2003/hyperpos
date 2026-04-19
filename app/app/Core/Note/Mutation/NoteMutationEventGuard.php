<?php

declare(strict_types=1);

namespace App\Core\Note\Mutation;

use App\Core\Shared\Exceptions\DomainException;

final class NoteMutationEventGuard
{
    public static function assertValid(
        string $id,
        string $noteId,
        string $mutationType,
        string $actorId,
        string $actorRole,
        string $reason,
    ): void {
        if (trim($id) === '') throw new DomainException('Note mutation event id wajib ada.');
        if (trim($noteId) === '') throw new DomainException('Note id wajib ada.');
        if (trim($mutationType) === '') throw new DomainException('Mutation type wajib ada.');
        if (trim($actorId) === '') throw new DomainException('Actor id wajib ada.');
        if (trim($actorRole) === '') throw new DomainException('Actor role wajib ada.');
        if (trim($reason) === '') throw new DomainException('Reason wajib ada.');
    }
}
