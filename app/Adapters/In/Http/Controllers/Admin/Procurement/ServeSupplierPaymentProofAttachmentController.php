<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Application\Procurement\Services\ServeSupplierPaymentProofAttachmentData;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

final class ServeSupplierPaymentProofAttachmentController extends Controller
{
    public function __invoke(
        Request $request,
        ServeSupplierPaymentProofAttachmentData $attachmentData,
        string $attachmentId,
    ): Response {
        $attachment = $attachmentData->getById($attachmentId);

        abort_if($attachment === null, 404);

        $disk = Storage::disk('local');
        $path = $attachment->storagePath();

        abort_unless($disk->exists($path), 404);

        $contentDisposition = $request->boolean('download')
            ? 'attachment'
            : 'inline';

        return response(
            $disk->get($path),
            200,
            [
                'Content-Type' => $attachment->mimeType(),
                'Content-Disposition' => $contentDisposition . '; filename="' . $attachment->originalFilename() . '"',
            ],
        );
    }
}
