<?php

namespace App\Console\Commands;

use App\Models\Procurement\Vendors\VendorBrochure;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MigrateBrochuresToS3Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'brochures:migrate-to-s3
                            {--delete-local : Delete local file after successful S3 upload}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate vendor brochure files from the local public disk to AWS S3.';

    public function __construct(private readonly FilesystemFactory $filesystem)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $records = VendorBrochure::query()
            ->whereNotNull('file_path')
            ->get();

        $total = $records->count();

        $this->info("Found {$total} vendor brochure record(s) to evaluate.");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $migrated = 0;
        $skipped  = 0;
        $failed   = 0;

        foreach ($records as $brochure) {
            $result = $this->migrateRecord($brochure, $migrated, $skipped, $failed);

            if ($result === 'migrated') {
                $migrated++;
            } elseif ($result === 'skipped') {
                $skipped++;
            } else {
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary output
        $this->info('Migration summary:');
        $this->table(
            ['Total evaluated', 'Migrated', 'Skipped', 'Failed'],
            [[$total, $migrated, $skipped, $failed]]
        );

        if ($failed > 0) {
            $this->warn("⚠  {$failed} record(s) failed. Please review the application log for details.");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Process a single VendorBrochure record.
     *
     * Returns 'migrated', 'skipped', or 'failed'.
     */
    private function migrateRecord(VendorBrochure $brochure, int &$migrated, int &$skipped, int &$failed): string
    {
        try {
            // Skip records with null or empty file_path
            if (empty($brochure->file_path)) {
                return 'skipped';
            }

            // Sanitize the path: remove whitespace, leading slashes, and known local prefixes
            $path = $this->sanitizePath($brochure->file_path);

            // If the file already exists on S3, skip it (idempotency)
            if (Storage::disk('s3')->exists($path)) {
                return 'skipped';
            }

            // Attempt upload to S3
            $uploaded = $this->uploadToS3($brochure->id, $path);

            if (! $uploaded) {
                return 'failed';
            }

            // Optionally delete the local copy
            if ($this->option('delete-local')) {
                $this->deleteLocal($brochure->id, $path);
            }

            return 'migrated';
        } catch (\Throwable $e) {
            Log::error('MigrateBrochuresToS3: unexpected exception while processing record.', [
                'id'        => $brochure->id,
                'file_path' => $brochure->file_path,
                'error'     => $e->getMessage(),
            ]);

            return 'failed';
        }
    }

    /**
     * Upload a file from the local public disk to S3.
     *
     * Returns true on success, false on any failure.
     */
    private function uploadToS3(int $id, string $path): bool
    {
        // Sanitize the path: remove whitespace, leading slashes, and known local prefixes
        $path = $this->sanitizePath($path);

        // Verify the file exists locally before attempting to read it
        if (! Storage::disk('public')->exists($path)) {
            Log::error('MigrateBrochuresToS3: local file not found.', [
                'id'        => $id,
                'file_path' => $path,
            ]);

            return false;
        }

        // Read the file stream from the local public disk
        $stream = Storage::disk('public')->readStream($path);

        if ($stream === false || $stream === null) {
            Log::error('MigrateBrochuresToS3: could not open local file stream.', [
                'id'        => $id,
                'file_path' => $path,
            ]);

            return false;
        }

        // Upload to S3 with public visibility
        $result = Storage::disk('s3')->put($path, $stream, ['visibility' => 'public']);

        if (is_resource($stream)) {
            fclose($stream);
        }

        if (! $result) {
            Log::error('MigrateBrochuresToS3: S3 upload failed.', [
                'id'        => $id,
                'file_path' => $path,
            ]);

            return false;
        }

        // Verify the file now exists on S3
        if (! Storage::disk('s3')->exists($path)) {
            Log::error('MigrateBrochuresToS3: post-upload S3 existence check failed.', [
                'id'        => $id,
                'file_path' => $path,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Normalize a raw file_path value coming from the database.
     *
     * Strips leading slashes and known local-disk prefixes so the result
     * is always a clean relative path suitable for both the public and S3 disks.
     *
     * Examples:
     *   "storage/app/public/vendors/brochures/1/file.pdf" → "vendors/brochures/1/file.pdf"
     *   "/vendors/brochures/1/file.pdf"                   → "vendors/brochures/1/file.pdf"
     *   "vendors/brochures/1/file.pdf"                    → "vendors/brochures/1/file.pdf"
     */
    private function sanitizePath(string $path): string
    {
        $path = trim($path);
        $path = ltrim($path, '/');

        // Strip known local-disk prefixes that should never be stored in file_path
        $prefixes = [
            'storage/app/public/',
            'storage/app/',
            'public/',
        ];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix));
                break;
            }
        }

        return $path;
    }

    /**
     * Delete a file from the local public disk.
     *
     * Logs a warning on failure but does not throw.
     */
    private function deleteLocal(int $id, string $path): void
    {
        $deleted = Storage::disk('public')->delete($path);

        if (! $deleted) {
            Log::warning('MigrateBrochuresToS3: failed to delete local file after successful S3 upload.', [
                'id'        => $id,
                'file_path' => $path,
            ]);
        }
    }
}
