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
            $rowNumber = $this->offset + $i + 2; // +2: 1-indexed + header row

            try {
                $result = $service->processRow($row);

                if ($result === 'imported') {
                    $imported++;
                } else {
                    $updated++;
                }
            } catch (Throwable $e) {
                $failed++;
                // Defensive second layer: even though readChunk() already
                // sanitizes every CSV value to valid UTF-8, guard the
                // error message itself too, since it's the thing that
                // gets json_encode()'d when the import row is saved —
                // one bad byte anywhere in this array fails the whole save.
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
            // No more rows — finalize, and write the downloadable error
            // CSV if anything failed (section 27).
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
