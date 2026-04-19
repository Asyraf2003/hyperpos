<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Supplier;

use App\Ports\Out\Procurement\SupplierReaderPort;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class EditSupplierPageController extends Controller
{
    public function __construct(
        private readonly SupplierReaderPort $suppliers,
    ) {
    }

    public function __invoke(string $supplierId): View|RedirectResponse
    {
        $supplier = $this->suppliers->getById($supplierId);

        if ($supplier === null) {
            return redirect()
                ->route('admin.suppliers.index')
                ->with('error', 'Supplier tidak ditemukan.');
        }

        return view('admin.suppliers.edit', [
            'supplier' => $supplier,
        ]);
    }
}
