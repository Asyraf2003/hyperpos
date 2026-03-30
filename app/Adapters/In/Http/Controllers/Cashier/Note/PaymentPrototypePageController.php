<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class PaymentPrototypePageController extends Controller
{
    public function __invoke(string $variant): View
    {
        $pages = [
            'a' => ['view' => 'cashier.notes.prototypes.payment-a', 'title' => 'Prototype A · Modal Action Cards'],
            'b' => ['view' => 'cashier.notes.prototypes.payment-b', 'title' => 'Prototype B · Dialog Stepper'],
            'c' => ['view' => 'cashier.notes.prototypes.payment-c', 'title' => 'Prototype C · POS Side Sheet'],
        ];

        abort_unless(isset($pages[$variant]), 404);

        return view($pages[$variant]['view'], [
            'pageTitle' => $pages[$variant]['title'],
            'prototypeLinks' => [
                'a' => route('cashier.notes.prototypes.payment', ['variant' => 'a']),
                'b' => route('cashier.notes.prototypes.payment', ['variant' => 'b']),
                'c' => route('cashier.notes.prototypes.payment', ['variant' => 'c']),
            ],
        ]);
    }
}
