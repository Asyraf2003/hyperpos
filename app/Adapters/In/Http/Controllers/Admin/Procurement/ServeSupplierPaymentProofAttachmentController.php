<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Ports\Out\Procurement\SupplierPaymentProofAttachmentReaderPort;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ServeSupplierPaymentProofAttachmentController extends Controller
{
    public function __invoke(
        Request $request,
        SupplierPaymentProofAttachmentReaderPort $attachments,
        string $attachmentId,
    ): StreamedResponse {
        $attachment = $attachments->getById(trim($attachmentId));

        abort_if($attachment === null, 404);

        $disk = Storage::disk('local');

        abort_unless($disk->exists($attachment->storagePath()), 404);

        $headers = [
            'Content-Type' => $attachment->mimeType(),
        ];

        if ($request->boolean('download')) {
            return $disk->download(
                $attachment->storagePath(),
                $attachment->originalFilename(),
                $headers,
            );
        }

        return $disk->response(
            $attachment->storagePath(),
            $attachment->originalFilename(),
            array_merge($headers, [
                'Content-Disposition' => 'inline; filename="' . $attachment->originalFilename() . '"',
            ]),
        );
    }
}
