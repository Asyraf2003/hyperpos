<?php

declare(strict_types=1);

namespace App\Application\MobileApi\Auth\DTO;

final readonly class MobileApiActor
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public string $role,
    ) {
    }

    /**
     * @return array{id:string,name:string,email:string,role:string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];
    }
}
