<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Note\Support;

use Illuminate\Http\Request;

final class NoteRouteAreaResolver
{
    public function showRoute(Request $request): string
    {
        return $this->isAdmin($request) ? 'admin.notes.show' : 'cashier.notes.show';
    }

    public function indexRoute(Request $request): string
    {
        return $this->isAdmin($request) ? 'admin.notes.index' : 'cashier.notes.index';
    }

    private function isAdmin(Request $request): bool
    {
        return $request->routeIs('admin.notes.*');
    }
}
