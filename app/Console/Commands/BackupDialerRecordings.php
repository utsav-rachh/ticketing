<?php
namespace App\Console\Commands;

use App\Models\DialerTicket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * If Smartping expires call recordings (item #11 in the integration reference
 * — still TBD), this pulls externally-hosted recording files onto our own
 * disk and rewrites recording_url to the local copy.
 *
 * No-op while no Smartping recording URLs exist, so it's safe to schedule.
 * Configure the target disk with SMARTPING_RECORDING_DISK.
 */
class BackupDialerRecordings extends Command
{
    protected $signature = 'dialer:backup-recordings {--limit=200 : Max tickets to process this run}';
    protected $description = 'Download Smartping call recordings to local storage and re-point recording_url.';

    public function handle(): int
    {
        $disk  = config('services.smartping.recording_disk', 'local');
        $limit = (int) $this->option('limit');

        $tickets = DialerTicket::query()
            ->whereNotNull('recording_url')
            ->where('recording_url', 'like', 'http%')
            ->where('recording_url', 'not like', '%/dialer-recordings/%')
            ->limit($limit)
            ->get();

        if ($tickets->isEmpty()) {
            $this->info('Nothing to back up.');
            return self::SUCCESS;
        }

        $ok = $fail = 0;
        foreach ($tickets as $ticket) {
            try {
                $res = Http::timeout(60)->get($ticket->recording_url);
                if ($res->failed()) { $fail++; $this->warn("[{$ticket->ticket_number}] HTTP {$res->status()}"); continue; }

                $ext  = pathinfo(parse_url($ticket->recording_url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: 'mp3';
                $path = "dialer-recordings/{$ticket->ticket_number}-".Str::random(8).".{$ext}";
                Storage::disk($disk)->put($path, $res->body());

                $ticket->update(['recording_url' => Storage::disk($disk)->url($path)]);
                $ticket->logEvent('recording_backed_up', ['disk' => $disk, 'path' => $path]);
                $ok++;
            } catch (\Throwable $e) {
                $fail++;
                $this->warn("[{$ticket->ticket_number}] {$e->getMessage()}");
            }
        }

        $this->info("Backed up {$ok} recording(s); {$fail} failed.");
        return self::SUCCESS;
    }
}
