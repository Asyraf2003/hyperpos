<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\DTO;

final class EmployeePayrollTableQuery
{
    public function __construct(
        private readonly int $page,
        private readonly int $perPage,
    ) {
    }

    public static function fromValidated(array $data): self
    {
        return new self(
            isset($data['page']) ? (int) $data['page'] : 1,
            isset($data['per_page']) ? (int) $data['per_page'] : 10,
        );
    }

    public function page(): int
    {
        return $this->page;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }
}
