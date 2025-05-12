<?php

namespace App\Jobs;

use App\Events\UploadProgressEvent;
use App\Models\Upload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessCsvUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $uploadId;

    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $uploadId)
    {
        $this->filePath = $filePath;
        $this->uploadId = $uploadId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $filePath = Storage::path($this->filePath);
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            Log::error("Failed to open file: {$this->filePath}");
            return;
        }

        Upload::where('id', $this->uploadId)->update(['status' => 'processing']);
        event(new UploadProgressEvent($this->uploadId, "processing"));

        $headerMap = [
            'UNIQUE_KEY' => 'unique_key',
            'PRODUCT_TITLE' => 'product_title',
            'PRODUCT_DESCRIPTION' => 'product_description',
            'STYLE#' => 'style',
            'SANMAR_MAINFRAME_COLOR' => 'sanmar_mainframe_color',
            'AVAILABLE_SIZES' => 'size',
            'COLOR_NAME' => 'color_name',
            'PIECE_PRICE' => 'piece_price'
        ];

        $csvHeader = fgetcsv($handle);
        if (!$csvHeader) {
            Log::error("Empty CSV header: {$this->filePath}");
            fclose($handle);
            return;
        }

        $csvHeader = array_map(fn($h) => mb_convert_encoding($h, 'UTF-8', 'UTF-8'), $csvHeader);
        $mappedHeader = array_map(fn($h) => $headerMap[$h] ?? $h, $csvHeader);

        foreach ($this->streamCsv($handle, $mappedHeader) as $chunk) {
            DB::table('products')->upsert($chunk, ['unique_key'], [
                'product_title',
                'product_description',
                'style',
                'sanmar_mainframe_color',
                'size',
                'color_name',
                'piece_price',
                'updated_at'
            ]);
        }

        fclose($handle);

        Upload::where('id', $this->uploadId)->update(['status' => 'completed']);
        event(new UploadProgressEvent($this->uploadId, "completed"));
    }

    /**
     * Generator that yields chunks of CSV rows
     */
    private function streamCsv($handle, array $mappedHeader, int $chunkSize = 500): \Generator
    {
        $allowedKeys = [
            'unique_key',
            'product_title',
            'product_description',
            'style',
            'sanmar_mainframe_color',
            'size',
            'color_name',
            'piece_price'
        ];

        $chunk = [];
        while (!feof($handle)) {
            $row = fgetcsv($handle);
            if (!$row || count($row) !== count($mappedHeader)) {
                continue;
            }

            $row = array_map(fn($v) => mb_convert_encoding($v, 'UTF-8', 'UTF-8'), $row);
            $entry = array_combine($mappedHeader, $row);

            // Ensure only allowed keys and required fields exist
            $entry = array_filter(
                $entry,
                fn($key) => in_array($key, $allowedKeys, true),
                ARRAY_FILTER_USE_KEY
            );

            if (empty($entry['unique_key'])) {
                // Optionally log the issue
                Log::warning("Row skipped due to missing unique_key", ['row' => $entry]);
                continue;
            }

            $now = now();
            $entry['created_at'] = $now;
            $entry['updated_at'] = $now;

            $chunk[] = $entry;

            if (count($chunk) >= $chunkSize) {
                yield $chunk;
                $chunk = [];
            }
        }

        if (!empty($chunk)) {
            yield $chunk;
        }
    }
}
