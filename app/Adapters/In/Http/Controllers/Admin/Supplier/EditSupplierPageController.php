<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Supplier;

use App\Application\Procurement\Services\EditSupplierPageData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class EditSupplierPageController extends Controller
{
    public function __construct(
        private readonly EditSupplierPageData $pageData,
    ) {
    }

    public function __invoke(string $supplierId): View|RedirectResponse
    {
        $supplier = $this->pageData->getById($supplierId);

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
