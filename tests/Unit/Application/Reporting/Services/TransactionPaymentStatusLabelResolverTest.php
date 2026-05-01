<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Reporting\Services;

use App\Application\Reporting\Services\TransactionPaymentStatusLabelResolver;
use PHPUnit\Framework\TestCase;

final class TransactionPaymentStatusLabelResolverTest extends TestCase
{
    public function test_it_resolves_indonesian_payment_status_labels(): void
    {
        $resolver = new TransactionPaymentStatusLabelResolver();

        $this->assertSame('Belum Dibayar', $resolver->resolve(100000, 0, 0));
        $this->assertSame('Sebagian', $resolver->resolve(100000, 40000, 0));
        $this->assertSame('Lunas', $resolver->resolve(100000, 100000, 0));
        $this->assertSame('Ada Refund', $resolver->resolve(100000, 100000, 20000));
        $this->assertSame('Refund Penuh', $resolver->resolve(100000, 100000, 100000));
    }
}
