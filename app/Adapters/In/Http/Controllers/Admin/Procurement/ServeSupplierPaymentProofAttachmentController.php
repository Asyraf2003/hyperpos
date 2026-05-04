<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Application\Procurement\UseCases\GetSupplierPaymentProofAttachmentFileHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

final class ServeSupplierPaymentProofAttachmentController extends Controller
{
    public function __invoke(
        Request $request,
        GetSupplierPaymentProofAttachmentFileHandler $handler,
        string $attachmentId,
    ): Response {
        $file = $handler->handle($attachmentId);

        abort_if($file === null, 404);

        $contentDisposition = $request->boolean('download')
            ? 'attachment'
            : 'inline';

        return response(
            $file->content(),
            200,
            [
                'Content-Type' => $file->mimeType(),
                'Content-Disposition' => $contentDisposition . '; filename="' . $file->originalFilename() . '"',
            ],
        );
    }
}
