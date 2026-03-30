<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use App\Application\Note\Services\CreateTransactionWorkspacePageDataBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class CreateTransactionWorkspacePageController extends Controller
{
    public function __invoke(CreateTransactionWorkspacePageDataBuilder $builder): View
    {
        $page = $builder->build();
        $oldNote = old('note');
        $oldItems = old('items');
        $oldInlinePayment = old('inline_payment');

        $defaultCustomerName = 'Pelanggan no 1';
        $productLookupEndpoint = route('cashier.notes.products.lookup');

        $workspaceConfigJson = json_encode([
            'oldItems' => is_array($oldItems) ? array_values($oldItems) : [],
            'defaultCustomerName' => $defaultCustomerName,
            'productLookupEndpoint' => $productLookupEndpoint,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return view('cashier.notes.workspace.create', [
            'pageTitle' => 'Buat Nota',
            'oldNote' => is_array($oldNote) ? $oldNote : [
                'customer_name' => $defaultCustomerName,
                'customer_phone' => '',
                'transaction_date' => date('Y-m-d'),
            ],
            'oldItems' => is_array($oldItems) ? array_values($oldItems) : [],
            'oldInlinePayment' => is_array($oldInlinePayment) ? $oldInlinePayment : [
                'decision' => 'skip',
                'payment_method' => 'cash',
                'paid_at' => date('Y-m-d'),
                'amount_paid_rupiah' => '',
                'amount_received_rupiah' => '',
                'notes' => '',
            ],
            'defaultCustomerName' => $defaultCustomerName,
            'productLookupEndpoint' => $productLookupEndpoint,
            'workspaceConfigJson' => is_string($workspaceConfigJson) ? $workspaceConfigJson : '{}',
        ] + $page);
    }
}
