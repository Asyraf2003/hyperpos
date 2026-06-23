<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Application\Procurement\UseCases\GetSupplierPaymentProofAttachmentFileHandler;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class PreviewSupplierPaymentProofAttachmentPageController extends Controller
{
    public function __invoke(
        Request $request,
        GetSupplierPaymentProofAttachmentFileHandler $handler,
        string $attachmentId,
    ): View {
        $file = $handler->handle($attachmentId);

        abort_if($file === null, 404);

        $mimeType = $file->mimeType();

        return view('admin.procurement.supplier_payment_proof_attachments.preview', [
            'attachmentId' => $attachmentId,
            'originalFilename' => $file->originalFilename(),
            'mimeType' => $mimeType,
            'isImagePreview' => str_starts_with($mimeType, 'image/'),
            'isPdfPreview' => $mimeType === 'application/pdf',
            'rawUrl' => route('admin.procurement.supplier-payment-proof-attachments.show', [
                'attachmentId' => $attachmentId,
            ]),
            'downloadUrl' => route('admin.procurement.supplier-payment-proof-attachments.show', [
                'attachmentId' => $attachmentId,
                'download' => 1,
            ]),
            'backUrl' => $this->safeBackUrl($request),
        ]);
    }

    private function safeBackUrl(Request $request): string
    {
        $candidate = (string) $request->query('back_url', '');

        if (
            $candidate !== ''
            && str_starts_with($candidate, '/')
            && ! str_starts_with($candidate, '//')
            && preg_match('/[\x00-\x1F\x7F]/', $candidate) !== 1
        ) {
            return $candidate;
        }

        return route('admin.procurement.supplier-invoices.index');
    }
}
