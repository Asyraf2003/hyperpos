<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class CreateTransactionWorkspaceMobileUiLabPageController extends Controller
{
    /** @var array<string, string> */
    private const VARIANTS = [
        '01' => 'Google Form Picker',
        '02' => 'Stepper Wizard',
        '03' => 'POS Keypad',
        '04' => 'Bottom Sheet Checkout',
        '05' => 'Accordion Checklist',
        '06' => 'Service Package Builder',
        '07' => 'Chat Style Intake',
        '08' => 'Live Receipt Split View',
        '09' => 'One-Hand Thumb UI',
        '10' => 'Dense Table Power User',
    ];

    public function __invoke(string $variant = '01'): View
    {
        $activeVariant = str_pad($variant, 2, '0', STR_PAD_LEFT);

        abort_unless(array_key_exists($activeVariant, self::VARIANTS), 404);

        return view('cashier.notes.workspace.mobile-ui-lab', [
            'pageTitle' => 'Create Transaction UI Lab',
            'activeVariant' => $activeVariant,
            'variants' => self::VARIANTS,
        ]);
    }
}
