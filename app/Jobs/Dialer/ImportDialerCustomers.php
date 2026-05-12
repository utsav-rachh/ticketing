<?php
namespace App\Jobs\Dialer;

use App\Models\CsvImport;
use App\Models\DialerCustomer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Parses an uploaded CSV of dialer customers and inserts them, skipping any
 * whose (normalised) phone number already exists. Counts are written back to
 * the csv_imports row.
 *
 * Accepted headers (case-insensitive, in any order): name, phone, email,
 * company, notes. A "phone" column is required; rows without a usable phone
 * are counted as failed.
 */
class ImportDialerCustomers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public function __construct(
        public int $importId,
        public string $storedPath,
        public ?int $userId = null,
    ) {}

    public function handle(): void
    {
        $import = CsvImport::find($this->importId);
        if (! $import) return;

        if (! Storage::exists($this->storedPath)) {
            $import->update(['status' => 'failed', 'error' => 'Uploaded file went missing.']);
            return;
        }

        $total = $imported = $duplicates = $failed = 0;
        $seenInFile = [];

        try {
            $handle = fopen(Storage::path($this->storedPath), 'r');
            if ($handle === false) throw new \RuntimeException('Could not open the CSV.');

            $header = null;
            while (($row = fgetcsv($handle)) !== false) {
                // Skip fully blank lines.
                if ($row === [null] || (count($row) === 1 && trim((string) $row[0]) === '')) continue;

                if ($header === null) {
                    $header = array_map(fn ($h) => strtolower(trim((string) $h)), $row);
                    continue;
                }

                $total++;
                $data  = array_combine($header, array_pad($row, count($header), null)) ?: [];
                $phone = DialerCustomer::normalizePhone($data['phone'] ?? '');
                $name  = trim((string) ($data['name'] ?? ''));

                if ($phone === '') { $failed++; continue; }
                if (isset($seenInFile[$phone]) || DialerCustomer::where('phone', $phone)->exists()) {
                    $duplicates++;
                    continue;
                }
                $seenInFile[$phone] = true;

                DialerCustomer::create([
                    'name'          => $name !== '' ? $name : $phone,
                    'phone'         => $phone,
                    'email'         => trim((string) ($data['email'] ?? '')) ?: null,
                    'company'       => trim((string) ($data['company'] ?? '')) ?: null,
                    'notes'         => trim((string) ($data['notes'] ?? '')) ?: null,
                    'imported_from' => $import->filename,
                    'created_by'    => $this->userId,
                ]);
                $imported++;
            }
            fclose($handle);

            $import->update([
                'total_rows' => $total,
                'imported'   => $imported,
                'duplicates' => $duplicates,
                'failed'     => $failed,
                'status'     => 'completed',
            ]);
        } catch (\Throwable $e) {
            Log::error('Dialer CSV import failed', ['import_id' => $this->importId, 'error' => $e->getMessage()]);
            $import->update([
                'total_rows' => $total,
                'imported'   => $imported,
                'duplicates' => $duplicates,
                'failed'     => $failed,
                'status'     => 'failed',
                'error'      => $e->getMessage(),
            ]);
        } finally {
            Storage::delete($this->storedPath);
        }
    }
}
