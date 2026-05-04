<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

interface SupplierPaymentProofFileStoragePort
{
    /**
     * @param list<array{
     * source_path:string,
     * original_filename:string,
     * mime_type:string,
     * file_size_bytes:int
     * }> $files
     * @return list<array{
     * storage_path:string,
     * original_filename:string,
     * mime_type:string,
     * file_size_bytes:int
     * }>
     */
    public function storeMany(string $supplierPaymentId, array $files): array;

    /**
     * @param list<string> $paths
     */
    public function deleteMany(array $paths): void;

    public function exists(string $path): bool;

    public function get(string $path): ?string;
}
