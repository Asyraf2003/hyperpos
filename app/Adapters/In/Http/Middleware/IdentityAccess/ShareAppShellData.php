<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Middleware\IdentityAccess;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ShareAppShellData
{
    public function __construct(
        private readonly AppShellDataBuilder $appShells,
        private readonly UiFeedbackDataBuilder $uiFeedbacks,
    ) {
    }

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $appShell = $this->appShells->build($request);
        $uiFeedback = $this->uiFeedbacks->build($request);
        $uiFeedbackJson = $uiFeedback === null
            ? 'null'
            : (string) json_encode(
                $uiFeedback,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT,
            );

        view()->share('appShell', $appShell);
        view()->share('uiFeedback', $uiFeedback);
        view()->share('uiFeedbackJson', $uiFeedbackJson);

        return $next($request);
    }
}
