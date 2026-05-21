<?php

namespace App\Console\Commands;

use App\Models\Procurement\ProcurementRequests\ProcurementRequestDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToCheckExistence;

class MigrateProcurementRequestDocumentsToS3Command extends Command
{
    protected $signature = 'procurement-request-documents:migrate-to-s3
                            {--delete-local : Delete local public-disk file after successful S3 upload}';

    protected $description = 'Migrate PR supporting documents from the local public disk to AWS S3.';

    public function handle(): int
    {
        set_time_limit(0);

        $records = ProcurementRequestDocument::query()
            ->whereNotNull('file_path')
            ->get();

        $total = $records->count();
        $this->info("Found {$total} document record(s) to evaluate.");

        $migrated = 0;
        $skipped = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($records as $document) {
            $result = $this->migrateRecord($document);

            match ($result) {
                'migrated' => $migrated++,
                'skipped' => $skipped++,
                default => $failed++,
            };

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Total', 'Migrated', 'Skipped', 'Failed'],
            [[$total, $migrated, $skipped, $failed]]
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function migrateRecord(ProcurementRequestDocument $document): string
    {
        try {
            if (empty($document->file_path)) {
                return 'skipped';
            }

            $path = $this->sanitizePath($document->file_path);

            if ($this->s3FileExists($path)) {
                return 'skipped';
            }

            if (! $this->uploadToS3($document->id, $path)) {
                return 'failed';
            }

            if ($this->option('delete-local')) {
                $this->deleteLocal($document->id, $path);
            }

            return 'migrated';
        } catch (\Throwable $e) {
            Log::error('MigrateProcurementRequestDocumentsToS3: unexpected exception.', [
                'id' => $document->id,
                'file_path' => $document->file_path,
                'error' => $e->getMessage(),
            ]);

            return 'failed';
        }
    }

    private function s3FileExists(string $path): bool
    {
        try {
            return Storage::disk('s3')->exists($path);
        } catch (UnableToCheckExistence $e) {
            Log::warning('MigrateProcurementRequestDocumentsToS3: cannot check S3 existence; will try upload.', [
                'file_path' => $path,
                'error' => $e->getPrevious()?->getMessage() ?? $e->getMessage(),
            ]);

            return false;
        }
    }

    private function uploadToS3(int $id, string $path): bool
    {
        if (! Storage::disk('public')->exists($path)) {
            Log::error('MigrateProcurementRequestDocumentsToS3: local file not found.', [
                'id' => $id,
                'file_path' => $path,
            ]);

            return false;
        }

        $stream = Storage::disk('public')->readStream($path);

        if ($stream === false || $stream === null) {
            Log::error('MigrateProcurementRequestDocumentsToS3: could not open local stream.', [
                'id' => $id,
                'file_path' => $path,
            ]);

            return false;
        }

        $result = Storage::disk('s3')->put($path, $stream, ['visibility' => 'public']);

        if (is_resource($stream)) {
            fclose($stream);
        }

        if (! $result) {
            Log::error('MigrateProcurementRequestDocumentsToS3: S3 upload failed.', [
                'id' => $id,
                'file_path' => $path,
            ]);

            return false;
        }

        return $this->verifyS3ObjectAfterPut($id, $path);
    }

    private function verifyS3ObjectAfterPut(int $id, string $path): bool
    {
        try {
            if (! Storage::disk('s3')->exists($path)) {
                Log::error('MigrateProcurementRequestDocumentsToS3: post-upload existence check failed.', [
                    'id' => $id,
                    'file_path' => $path,
                ]);

                return false;
            }

            return true;
        } catch (UnableToCheckExistence $e) {
            Log::warning('MigrateProcurementRequestDocumentsToS3: post-upload check unavailable; treating upload as success.', [
                'id' => $id,
                'file_path' => $path,
                'error' => $e->getPrevious()?->getMessage() ?? $e->getMessage(),
            ]);

            return true;
        }
    }

    private function sanitizePath(string $path): string
    {
        $path = trim($path);
        $path = ltrim($path, '/');

        foreach (['storage/app/public/', 'storage/app/', 'public/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix));
                break;
            }
        }

        return $path;
    }

    private function deleteLocal(int $id, string $path): void
    {
        if (! Storage::disk('public')->delete($path)) {
            Log::warning('MigrateProcurementRequestDocumentsToS3: failed to delete local file.', [
                'id' => $id,
                'file_path' => $path,
            ]);
        }
    }
}
