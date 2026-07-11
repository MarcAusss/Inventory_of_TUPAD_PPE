<?php

namespace App\Services;

use App\Models\PdfTemplate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use setasign\Fpdi\Fpdi;
use Throwable;

class PdfTemplateService extends BaseService
{
    /**
     * Upload a new PDF template version.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(
        array $data,
        UploadedFile $pdfFile
    ): PdfTemplate {
        $this->requireTssd();

        $storedPath = null;

        try {
            $storedPath = $pdfFile->store(
                'pdf-templates',
                'public'
            );

            if (! $storedPath) {
                throw new \RuntimeException(
                    'The PDF template could not be stored.'
                );
            }

            $absolutePath = Storage::disk('public')
                ->path($storedPath);

            $pageCount = $this->readPageCount(
                $absolutePath
            );

            $fileHash = hash_file(
                'sha256',
                $absolutePath
            );

            return DB::transaction(
                function () use ($data, $pdfFile, $storedPath, $pageCount, $fileHash): PdfTemplate {
                    $version = $this->nextVersion(
                        $data['report_type']
                    );

                    if ($data['is_active'] ?? false) {
                        $this->deactivateReportTemplates(
                            $data['report_type']
                        );
                    }

                    return PdfTemplate::create([
                        'template_name' => $data['template_name'],

                        'report_type' => $data['report_type'],

                        'original_filename' => $pdfFile->getClientOriginalName(),

                        'pdf_path' => $storedPath,

                        'file_size' => $pdfFile->getSize(),

                        'page_count' => $pageCount,

                        'file_hash' => $fileHash,

                        'version' => $version,

                        'is_active' => $data['is_active'] ?? false,

                        'description' => $data['description'] ?? null,

                        'uploaded_by' => $this->userId(),

                        'updated_by' => $this->userId(),
                    ]);
                }
            );
        } catch (Throwable $exception) {
            $this->deleteFile($storedPath);

            throw $exception;
        }
    }

    /**
     * Update metadata or replace the PDF file.
     *
     * Replacing a PDF keeps the same database version record.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(
        PdfTemplate $pdfTemplate,
        array $data,
        ?UploadedFile $pdfFile
    ): PdfTemplate {
        $this->requireTssd();

        $oldPath = $pdfTemplate->pdf_path;
        $newPath = null;

        try {
            $newFileData = [];

            if ($pdfFile) {
                $newPath = $pdfFile->store(
                    'pdf-templates',
                    'public'
                );

                if (! $newPath) {
                    throw new \RuntimeException(
                        'The replacement PDF could not be stored.'
                    );
                }

                $absolutePath = Storage::disk('public')
                    ->path($newPath);

                $newFileData = [
                    'original_filename' => $pdfFile->getClientOriginalName(),

                    'pdf_path' => $newPath,

                    'file_size' => $pdfFile->getSize(),

                    'page_count' => $this->readPageCount(
                        $absolutePath
                    ),

                    'file_hash' => hash_file(
                        'sha256',
                        $absolutePath
                    ),
                ];
            }

            DB::transaction(
                function () use ($pdfTemplate, $data, $newFileData): void {
                    if ($data['is_active'] ?? false) {
                        $this->deactivateReportTemplates(
                            $data['report_type'],
                            $pdfTemplate->id
                        );
                    }

                    $pdfTemplate->update([
                        'template_name' => $data['template_name'],

                        'report_type' => $data['report_type'],

                        'description' => $data['description'] ?? null,

                        'is_active' => $data['is_active'] ?? false,

                        'updated_by' => $this->userId(),

                        ...$newFileData,
                    ]);
                }
            );

            if (
                $newPath
                && $oldPath !== $newPath
            ) {
                $this->deleteFile($oldPath);
            }

            return $pdfTemplate->fresh([
                'uploader',
                'updater',
            ]);
        } catch (Throwable $exception) {
            $this->deleteFile($newPath);

            throw $exception;
        }
    }

    public function activate(
        PdfTemplate $pdfTemplate
    ): PdfTemplate {
        $this->requireTssd();

        DB::transaction(
            function () use ($pdfTemplate): void {
                $this->deactivateReportTemplates(
                    $pdfTemplate->report_type,
                    $pdfTemplate->id
                );

                $pdfTemplate->update([
                    'is_active' => true,
                    'updated_by' => $this->userId(),
                ]);
            }
        );

        return $pdfTemplate->fresh();
    }

    public function deactivate(
        PdfTemplate $pdfTemplate
    ): PdfTemplate {
        $this->requireTssd();

        $pdfTemplate->update([
            'is_active' => false,
            'updated_by' => $this->userId(),
        ]);

        return $pdfTemplate->fresh();
    }

    public function delete(
        PdfTemplate $pdfTemplate
    ): void {
        $this->requireTssd();

        if ($pdfTemplate->is_active) {
            throw ValidationException::withMessages([
                'pdf_template' => 'An active PDF template cannot be deleted. Deactivate it first.',
            ]);
        }

        $path = $pdfTemplate->pdf_path;

        DB::transaction(
            function () use ($pdfTemplate): void {
                $pdfTemplate->delete();
            }
        );

        $this->deleteFile($path);
    }

    /**
     * Used by all roles when generating reports.
     */
    public function activeForReport(
        string $reportType
    ): ?PdfTemplate {
        return PdfTemplate::query()
            ->forReport($reportType)
            ->active()
            ->latest('version')
            ->first();
    }

    private function nextVersion(
        string $reportType
    ): int {
        return ((int) PdfTemplate::query()
            ->forReport($reportType)
            ->max('version')) + 1;
    }

    private function deactivateReportTemplates(
        string $reportType,
        ?int $exceptId = null
    ): void {
        PdfTemplate::query()
            ->forReport($reportType)
            ->when(
                $exceptId,
                fn ($query) => $query->whereKeyNot($exceptId)
            )
            ->update([
                'is_active' => false,
                'updated_by' => $this->userId(),
            ]);
    }

    private function readPageCount(
        string $absolutePath
    ): int {
        $pdf = new Fpdi;

        return $pdf->setSourceFile(
            $absolutePath
        );
    }

    private function deleteFile(
        ?string $path
    ): void {
        if (
            $path
            && Storage::disk('public')->exists($path)
        ) {
            Storage::disk('public')->delete($path);
        }
    }
}
