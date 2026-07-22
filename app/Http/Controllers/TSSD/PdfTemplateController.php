<?php

namespace App\Http\Controllers\TSSD;

use App\Http\Controllers\Controller;
use App\Http\Requests\TSSD\StorePdfTemplateRequest;
use App\Http\Requests\TSSD\UpdatePdfTemplateRequest;
use App\Models\PdfTemplate;
use App\Services\PdfTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PdfTemplateController extends Controller
{
    /**
     * Display all uploaded PDF templates.
     */
    public function index(Request $request): View
    {
        $reportType = trim(
            (string) $request->query('report_type')
        );

        $status = trim(
            (string) $request->query('status')
        );

        $search = trim(
            (string) $request->query('search')
        );

        $templates = PdfTemplate::query()
            ->with([
                'uploader',
                'updater',
            ])
            ->when(
                $reportType,
                fn ($query) => $query->where(
                    'report_type',
                    $reportType
                )
            )
            ->when(
                $status === 'active',
                fn ($query) => $query->where(
                    'is_active',
                    true
                )
            )
            ->when(
                $status === 'inactive',
                fn ($query) => $query->where(
                    'is_active',
                    false
                )
            )
            ->when(
                $search,
                function ($query) use ($search): void {
                    $query->where(
                        function ($query) use ($search): void {
                            $query
                                ->where(
                                    'template_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'original_filename',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'description',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->orderBy('report_type')
            ->orderByDesc('version')
            ->paginate(15)
            ->withQueryString();

        $reportTypeOptions =
            PdfTemplate::reportTypeOptions();

        return view(
            'tssd.pdf-templates.index',
            compact(
                'templates',
                'reportTypeOptions',
                'reportType',
                'status',
                'search'
            )
        );
    }

    /**
     * Show the upload form.
     */
    public function create(): View
    {
        $reportTypeOptions =
            PdfTemplate::reportTypeOptions();

        return view(
            'tssd.pdf-templates.create',
            compact('reportTypeOptions')
        );
    }

    /**
     * Upload a new template version.
     */
    public function store(
        StorePdfTemplateRequest $request,
        PdfTemplateService $service
    ): RedirectResponse {
        $template = $service->create(
            $request->validated(),
            $request->file('pdf_file')
        );

        return redirect()
            ->route(
                'tssd.pdf-templates.show',
                $template
            )
            ->with(
                'success',
                'PDF template uploaded successfully.'
            );
    }

    /**
     * Display template information.
     */
    public function show(
        PdfTemplate $pdfTemplate
    ): View {
        $pdfTemplate->load([
            'uploader',
            'updater',
        ]);

        return view(
            'tssd.pdf-templates.show',
            compact('pdfTemplate')
        );
    }

    /**
     * Show template edit form.
     */
    public function edit(
        PdfTemplate $pdfTemplate
    ): View {
        $reportTypeOptions =
            PdfTemplate::reportTypeOptions();

        return view(
            'tssd.pdf-templates.edit',
            compact(
                'pdfTemplate',
                'reportTypeOptions'
            )
        );
    }

    /**
     * Update metadata or replace the PDF.
     */
    public function update(
        UpdatePdfTemplateRequest $request,
        PdfTemplate $pdfTemplate,
        PdfTemplateService $service
    ): RedirectResponse {
        $template = $service->update(
            $pdfTemplate,
            $request->validated(),
            $request->file('pdf_file')
        );

        return redirect()
            ->route(
                'tssd.pdf-templates.show',
                $template
            )
            ->with(
                'success',
                'PDF template updated successfully.'
            );
    }

    /**
     * Display the uploaded PDF in the browser.
     */
    public function preview(
        PdfTemplate $pdfTemplate,
        PdfTemplateService $service
    ): BinaryFileResponse {
        $disk = $service->diskContaining($pdfTemplate->pdf_path);
        abort_unless($disk, 404, 'The uploaded PDF file could not be found.');

        return response()->file(
            \Illuminate\Support\Facades\Storage::disk($disk)->path($pdfTemplate->pdf_path),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store, max-age=0',
            ]
        );
    }

    /**
     * Download the uploaded PDF.
     */
    public function download(
        PdfTemplate $pdfTemplate,
        PdfTemplateService $service
    ): BinaryFileResponse {
        $disk = $service->diskContaining($pdfTemplate->pdf_path);
        abort_unless($disk, 404, 'The uploaded PDF file could not be found.');

        return response()->download(
            \Illuminate\Support\Facades\Storage::disk($disk)->path($pdfTemplate->pdf_path),
            $pdfTemplate->original_filename,
            [
                'Content-Type' => 'application/pdf',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store, max-age=0',
            ]
        );
    }

    /**
     * Activate this template for its assigned report.
     */
    public function activate(
        PdfTemplate $pdfTemplate,
        PdfTemplateService $service
    ): RedirectResponse {
        $service->activate($pdfTemplate);

        return back()->with(
            'success',
            'PDF template activated. Other templates assigned to the same report were deactivated.'
        );
    }

    /**
     * Deactivate this template.
     */
    public function deactivate(
        PdfTemplate $pdfTemplate,
        PdfTemplateService $service
    ): RedirectResponse {
        $service->deactivate($pdfTemplate);

        return back()->with(
            'success',
            'PDF template deactivated.'
        );
    }

    /**
     * Delete an inactive template.
     */
    public function destroy(
        PdfTemplate $pdfTemplate,
        PdfTemplateService $service
    ): RedirectResponse {
        $service->delete($pdfTemplate);

        return redirect()
            ->route('tssd.pdf-templates.index')
            ->with(
                'success',
                'PDF template deleted successfully.'
            );
    }
}
