<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Application\Procurement\Services\ServeSupplierPaymentProofAttachmentData;
use App\Ports\Out\Procurement\SupplierPaymentProofFileStoragePort;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

final class ServeSupplierPaymentProofAttachmentController extends Controller
{
    public function __invoke(
        Request $request,
        ServeSupplierPaymentProofAttachmentData $attachmentData,
        SupplierPaymentProofFileStoragePort $files,
        string $attachmentId,
    ): Response {
        $attachment = $attachmentData->getById($attachmentId);

        abort_if($attachment === null, 404);

        $path = $attachment->storagePath();

        abort_unless($files->exists($path), 404);

        $content = $files->get($path);

        abort_if($content === null, 404);

        $contentDisposition = $request->boolean('download')
            ? 'attachment'
            : 'inline';

        return response(
            $content,
            200,
            [
                'Content-Type' => $attachment->mimeType(),
                'Content-Disposition' => $contentDisposition . '; filename="' . $attachment->originalFilename() . '"',
            ],
        );
    }
}
