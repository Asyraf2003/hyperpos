<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class RegisterPageController extends Controller
{
    public function __invoke(): View
    {
        return view('auth.register');
    }
}
