<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\AuditLog;

use App\Ports\Out\AuditLogReaderPort;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class AuditLogIndexPageController extends Controller
{
    public function __invoke(Request $request, AuditLogReaderPort $reader): View
    {
        $queryValue = $request->query('q', '');
        $search = is_string($queryValue) ? trim($queryValue) : '';

        return view('admin.audit_logs.index', [
            'logs' => $reader->listForAdmin($search, 20),
            'search' => $search,
        ]);
    }
}
