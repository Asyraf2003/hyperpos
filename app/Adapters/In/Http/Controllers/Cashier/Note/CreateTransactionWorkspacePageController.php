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

        return view('cashier.notes.workspace.create', [
            'pageTitle' => 'Buat Nota',
            'oldNote' => is_array($oldNote) ? $oldNote : [
                'customer_name' => 'Pelanggan no 1',
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
            'defaultCustomerName' => 'Pelanggan no 1',
            'productLookupEndpoint' => route('cashier.notes.products.lookup'),
        ] + $page);
    }
}
