<?php

namespace App\Jobs;

use App\Models\ProductImport;
use App\Services\ProductImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Section 29: "Don't process 5,000 images in one request." Each job
 * instance handles one chunk (100 rows), then dispatches the next chunk
 * — never the whole file in one PHP process. On Hostinger, this relies
 * on the database queue being drained by cron (see PHASE8_NOTES.md).
 *
 * NOTE ON QUEUE_CONNECTION=sync (local dev default): with sync, jobs
 * execute immediately and inline, including the self-dispatch of the
 * NEXT chunk — meaning a large import with many image downloads can
 * still run past PHP's max_execution_time, because the whole chain
 * happens inside one HTTP request no matter how it's chunked. The
 * set_time_limit(0) below removes that cap for this job specifically.
 * For imports of any real size, switch QUEUE_CONNECTION=database and
 * run `php artisan queue:work` in a second terminal instead — each
 * chunk then runs as its own process with its own time budget, which is
 * both faster to see progress on and how production actually works.
 */
class ProcessProductImportChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $chunkSize = 100;

    public int $tries = 1;
    public int $timeout = 300;

    public function __construct(protected int $importId, protected int $offset)
    {
    }

    public function handle(ProductImportService $service): void
    {
        // Only relevant under QUEUE_CONNECTION=sync — a real queue worker
        // already runs each job as its own process, so this is a no-op
        // there. Harmless either way.
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        $import = ProductImport::find($this->importId);

        if (! $import) {
            return;
        }

        $import->update(['status' => 'processing']);

        $handle = fopen(Storage::disk('local')->path($import->file_path), 'r');
        $headers = fgetcsv($handle);
        fclose($handle);

        $rows = $service->readChunk($import->file_path, $headers, $import->column_mapping, $this->offset, $this->chunkSize);

        $errors = $import->errors ?? [];
        $imported = $import->imported_count;
        $updated = $import->updated_count;
        $failed = $import->failed_count;

        foreach ($rows as $i => $row) {
            $rowNumber = $this->offset + $i + 2;

            try {
                $outcome = $service->processRow($row);

                if ($outcome['result'] === 'imported') {
                    $imported++;
                } else {
                    $updated++;
                }

                foreach ($outcome['warnings'] as $warning) {
                    $errors[] = ['row' => $rowNumber, 'message' => $this->sanitizeMessage($warning)];
                }
            } catch (Throwable $e) {
                $failed++;
                $errors[] = ['row' => $rowNumber, 'message' => $this->sanitizeMessage($e->getMessage())];
            }
        }

        $processed = $import->processed_rows + count($rows);

        $import->update([
            'processed_rows' => $processed,
            'imported_count' => $imported,
            'updated_count' => $updated,
            'failed_count' => $failed,
            'errors' => $errors,
        ]);

        if (count($rows) < $this->chunkSize || $processed >= $import->total_rows) {
            $import->update(['status' => 'completed']);

            if (! empty($errors)) {
                $service->writeErrorCsv($import->fresh());
            }
        } else {
            self::dispatch($this->importId, $this->offset + $this->chunkSize);
        }
    }

    protected function sanitizeMessage(string $message): string
    {
        if (mb_check_encoding($message, 'UTF-8')) {
            return $message;
        }

        $converted = @mb_convert_encoding($message, 'UTF-8', 'Windows-1252');

        return $converted !== false ? $converted : mb_convert_encoding($message, 'UTF-8', 'UTF-8');
    }
}