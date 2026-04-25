<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Support;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

final class ReportArrayPaginator
{
    private const DEFAULT_PER_PAGE = 15;

    public function paginate(
        array $rows,
        Request $request,
        string $pageName,
        int $perPage = self::DEFAULT_PER_PAGE,
    ): LengthAwarePaginator {
        $rawPage = $request->query($pageName);
        $page = is_scalar($rawPage) ? max(1, (int) $rawPage) : 1;
        $offset = ($page - 1) * $perPage;

        $paginator = new LengthAwarePaginator(
            array_slice($rows, $offset, $perPage),
            count($rows),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => $pageName,
            ],
        );

        $query = $request->query();

        return $paginator->appends(is_array($query) ? $query : []);
    }
}
