<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\CallOff;
use App\Models\DeliveryReceipt;
use App\Models\DeliveryReceiptDocument;
use App\Models\PurchaseOrder;
use App\Models\SupplyDesignation;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecureDocumentController extends Controller
{
    public function purchaseOrder(Request $request, PurchaseOrder $purchaseOrder): StreamedResponse
    {
        $this->authorizeRole($request, ['Supply Unit', 'TSSD Unit', 'Accounting Unit']);

        return $this->download($purchaseOrder->document, "PO-{$purchaseOrder->po_number}");
    }

    public function callOff(Request $request, CallOff $callOff): StreamedResponse
    {
        $this->authorizeRole($request, ['Supply Unit', 'TSSD Unit', 'Accounting Unit']);

        return $this->download($callOff->approval_document, "Call-Off-{$callOff->call_off_number}");
    }

    public function receiptDocument(Request $request, DeliveryReceiptDocument $document): StreamedResponse
    {
        $document->loadMissing('deliveryReceipt');
        $this->authorizeProvinceDocument($request, $document->deliveryReceipt?->province_id);

        return $this->download($document->file_path, $document->original_name);
    }

    public function receiptLegacy(Request $request, DeliveryReceipt $deliveryReceipt): StreamedResponse
    {
        $this->authorizeProvinceDocument($request, $deliveryReceipt->province_id);

        return $this->download($deliveryReceipt->document, "DR-{$deliveryReceipt->dr_number}");
    }

    public function are(Request $request, SupplyDesignation $supplyDesignation): StreamedResponse
    {
        $this->authorizeProvinceDocument($request, $supplyDesignation->province_id);

        return $this->download($supplyDesignation->are_document, "ARE-{$supplyDesignation->project_code}.pdf");
    }

    private function authorizeProvinceDocument(Request $request, ?int $provinceId): void
    {
        $role = $request->user()?->role?->name;

        if (in_array($role, ['TSSD Unit', 'Accounting Unit'], true)) {
            return;
        }

        abort_unless(
            $role === 'Provincial Office'
                && $provinceId !== null
                && (int) $request->user()->province_id === (int) $provinceId,
            403,
            'You are not authorized to access this document.'
        );
    }

    private function authorizeRole(Request $request, array $roles): void
    {
        abort_unless(
            in_array($request->user()?->role?->name, $roles, true),
            403,
            'You are not authorized to access this document.'
        );
    }

    private function download(?string $path, string $downloadName): StreamedResponse
    {
        abort_if(blank($path), 404, 'Document not found.');

        [$disk, $filesystem] = $this->locate($path);
        abort_unless($filesystem, 404, 'Document not found.');

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $safeName = preg_replace('/[^A-Za-z0-9._-]+/', '-', $downloadName) ?: 'document';
        if ($extension && ! str_ends_with(strtolower($safeName), '.'.strtolower($extension))) {
            $safeName .= '.'.$extension;
        }

        return Storage::disk($disk)->download($path, $safeName, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ]);
    }

    /** @return array{0:string,1:Filesystem|null} */
    private function locate(string $path): array
    {
        foreach (['local', 'public'] as $disk) {
            $filesystem = Storage::disk($disk);
            if ($filesystem->exists($path)) {
                return [$disk, $filesystem];
            }
        }

        return ['local', null];
    }
}
