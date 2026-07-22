<?php

namespace App\Services;

use App\Models\PdfTemplate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use setasign\Fpdi\Fpdi;
use Throwable;

class PdfTemplateService extends BaseService
{
    /** @param array<string, mixed> $data */
    public function create(array $data, UploadedFile $pdfFile): PdfTemplate
    {
        $this->requireTssd();
        $storedPath = null;
        $disk = $this->writeDisk();

        try {
            $this->validateUploadedPdf($pdfFile);
            $storedPath = $pdfFile->store('pdf-templates', $disk);

            if (! $storedPath) {
                throw new \RuntimeException('The PDF template could not be stored.');
            }

            $absolutePath = Storage::disk($disk)->path($storedPath);
            $pageCount = $this->readPageCount($absolutePath);
            $fileHash = hash_file('sha256', $absolutePath);

            if ($fileHash === false) {
                throw new \RuntimeException('The uploaded PDF could not be verified.');
            }

            return DB::transaction(function () use ($data, $pdfFile, $storedPath, $pageCount, $fileHash): PdfTemplate {
                $version = $this->nextVersion($data['report_type']);

                if ($data['is_active'] ?? false) {
                    $this->deactivateReportTemplates($data['report_type']);
                }

                return PdfTemplate::create([
                    'template_name' => $data['template_name'],
                    'report_type' => $data['report_type'],
                    'original_filename' => $this->safeOriginalFilename($pdfFile),
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
            });
        } catch (ValidationException $exception) {
            $this->deleteFileFromDisk($disk, $storedPath);
            throw $exception;
        } catch (Throwable $exception) {
            $this->deleteFileFromDisk($disk, $storedPath);
            Log::warning('PDF template upload rejected.', [
                'user_id' => $this->userId(),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'pdf_file' => 'The PDF could not be safely processed. Please upload a valid, non-encrypted PDF.',
            ]);
        }
    }

    /** @param array<string, mixed> $data */
    public function update(PdfTemplate $pdfTemplate, array $data, ?UploadedFile $pdfFile): PdfTemplate
    {
        $this->requireTssd();
        $oldPath = $pdfTemplate->pdf_path;
        $oldDisk = $this->diskContaining($oldPath);
        $newPath = null;
        $newDisk = $this->writeDisk();

        try {
            $newFileData = [];

            if ($pdfFile) {
                $this->validateUploadedPdf($pdfFile);
                $newPath = $pdfFile->store('pdf-templates', $newDisk);

                if (! $newPath) {
                    throw new \RuntimeException('The replacement PDF could not be stored.');
                }

                $absolutePath = Storage::disk($newDisk)->path($newPath);
                $fileHash = hash_file('sha256', $absolutePath);

                if ($fileHash === false) {
                    throw new \RuntimeException('The replacement PDF could not be verified.');
                }

                $newFileData = [
                    'original_filename' => $this->safeOriginalFilename($pdfFile),
                    'pdf_path' => $newPath,
                    'file_size' => $pdfFile->getSize(),
                    'page_count' => $this->readPageCount($absolutePath),
                    'file_hash' => $fileHash,
                ];
            }

            DB::transaction(function () use ($pdfTemplate, $data, $newFileData): void {
                if ($data['is_active'] ?? false) {
                    $this->deactivateReportTemplates($data['report_type'], $pdfTemplate->id);
                }

                $pdfTemplate->update([
                    'template_name' => $data['template_name'],
                    'report_type' => $data['report_type'],
                    'description' => $data['description'] ?? null,
                    'is_active' => $data['is_active'] ?? false,
                    'updated_by' => $this->userId(),
                    ...$newFileData,
                ]);
            });

            if ($newPath && $oldPath !== $newPath && $oldDisk) {
                $this->deleteFileFromDisk($oldDisk, $oldPath);
            }

            return $pdfTemplate->fresh(['uploader', 'updater']);
        } catch (ValidationException $exception) {
            $this->deleteFileFromDisk($newDisk, $newPath);
            throw $exception;
        } catch (Throwable $exception) {
            $this->deleteFileFromDisk($newDisk, $newPath);
            Log::warning('PDF template replacement rejected.', [
                'template_id' => $pdfTemplate->id,
                'user_id' => $this->userId(),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'pdf_file' => 'The PDF could not be safely processed. Please upload a valid, non-encrypted PDF.',
            ]);
        }
    }

    public function activate(PdfTemplate $pdfTemplate): PdfTemplate
    {
        $this->requireTssd();
        DB::transaction(function () use ($pdfTemplate): void {
            $this->deactivateReportTemplates($pdfTemplate->report_type, $pdfTemplate->id);
            $pdfTemplate->update(['is_active' => true, 'updated_by' => $this->userId()]);
        });
        return $pdfTemplate->fresh();
    }

    public function deactivate(PdfTemplate $pdfTemplate): PdfTemplate
    {
        $this->requireTssd();
        $pdfTemplate->update(['is_active' => false, 'updated_by' => $this->userId()]);
        return $pdfTemplate->fresh();
    }

    public function delete(PdfTemplate $pdfTemplate): void
    {
        $this->requireTssd();

        if ($pdfTemplate->is_active) {
            throw ValidationException::withMessages([
                'pdf_template' => 'An active PDF template cannot be deleted. Deactivate it first.',
            ]);
        }

        $path = $pdfTemplate->pdf_path;
        $disk = $this->diskContaining($path);
        DB::transaction(fn () => $pdfTemplate->delete());

        if ($disk) {
            $this->deleteFileFromDisk($disk, $path);
        }
    }

    public function activeForReport(string $reportType): ?PdfTemplate
    {
        return PdfTemplate::query()->forReport($reportType)->active()->latest('version')->first();
    }

    public function diskContaining(string $path): ?string
    {
        foreach (array_unique([$this->writeDisk(), $this->legacyDisk()]) as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return $disk;
            }
        }
        return null;
    }

    private function validateUploadedPdf(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            $this->invalidPdf('The PDF upload did not complete successfully.');
        }

        $size = (int) $file->getSize();
        if ($size < 8 || $size > (int) config('pdf_security.max_bytes', 20 * 1024 * 1024)) {
            $this->invalidPdf('The PDF file size is not allowed.');
        }

        $path = $file->getRealPath();
        if (! is_string($path) || ! is_readable($path)) {
            $this->invalidPdf('The uploaded PDF could not be read.');
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            $this->invalidPdf('The uploaded PDF could not be opened.');
        }

        try {
            $header = fread($handle, 5);
            if ($header !== '%PDF-') {
                $this->invalidPdf('The uploaded file does not have a valid PDF signature.');
            }

            fseek($handle, max(0, $size - 2048));
            $tail = stream_get_contents($handle);
            if (! is_string($tail) || ! str_contains($tail, '%%EOF')) {
                $this->invalidPdf('The PDF appears incomplete or malformed.');
            }
        } finally {
            fclose($handle);
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        if ($mime !== 'application/pdf') {
            $this->invalidPdf('The uploaded content is not recognized as a PDF.');
        }
    }

    private function readPageCount(string $absolutePath): int
    {
        try {
            $pageCount = (new Fpdi)->setSourceFile($absolutePath);
        } catch (Throwable $exception) {
            Log::notice('FPDI rejected an uploaded PDF.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
            $this->invalidPdf('The PDF is encrypted, damaged, or unsupported.');
        }

        $maxPages = max(1, (int) config('pdf_security.max_pages', 50));
        if ($pageCount < 1 || $pageCount > $maxPages) {
            $this->invalidPdf("The PDF must contain between 1 and {$maxPages} pages.");
        }

        return $pageCount;
    }

    private function safeOriginalFilename(UploadedFile $file): string
    {
        $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $name = preg_replace('/[^A-Za-z0-9._() -]/u', '_', $name) ?: 'template.pdf';
        return mb_substr($name, 0, 180);
    }

    private function nextVersion(string $reportType): int
    {
        return ((int) PdfTemplate::query()->forReport($reportType)->max('version')) + 1;
    }

    private function deactivateReportTemplates(string $reportType, ?int $exceptId = null): void
    {
        PdfTemplate::query()->forReport($reportType)
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->update(['is_active' => false, 'updated_by' => $this->userId()]);
    }

    private function writeDisk(): string
    {
        return (string) config('pdf_security.disk', 'local');
    }

    private function legacyDisk(): string
    {
        return (string) config('pdf_security.legacy_disk', 'public');
    }

    private function deleteFileFromDisk(string $disk, ?string $path): void
    {
        if ($path && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }

    private function invalidPdf(string $message): never
    {
        throw ValidationException::withMessages(['pdf_file' => $message]);
    }
}
