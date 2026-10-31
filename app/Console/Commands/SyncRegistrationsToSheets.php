<?php

namespace App\Console\Commands;

use App\Services\GoogleSheetsClient;
use App\Services\RegistrationSheetMirror;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncRegistrationsToSheets extends Command
{
    protected $signature = 'sheets:sync-registrations
                            {--dry-run : List the tabs and row counts without calling Google}';

    protected $description = 'Mirror the seminar and hands-on registration exports into Google Sheets';

    public function handle(RegistrationSheetMirror $mirror): int
    {
        // Scheduled unconditionally so the schedule stays predictable; an
        // unconfigured install simply does nothing.
        if (! config('services.google_sheets.enabled')) {
            $this->info('Google Sheets sync is disabled (set GOOGLE_SHEETS_SYNC_ENABLED=true).');

            return self::SUCCESS;
        }

        try {
            $tables = $mirror->tables();

            if ($this->option('dry-run')) {
                foreach ($tables as $table) {
                    $this->line(sprintf('%-45s %6d rows', $table['title'], count($table['rows'])));
                }

                return self::SUCCESS;
            }

            $client = GoogleSheetsClient::fromConfig();
            $this->push($client, $tables);

            $this->info(sprintf('Synced %d tabs to Google Sheets.', count($tables)));
        } catch (Throwable $exception) {
            Log::error('Google Sheets sync failed', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            $this->error('Google Sheets sync failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * A full replace: every wanted tab is cleared and rewritten, then tabs that
     * no longer correspond to anything are removed. Re-running converges on the
     * same sheet, so a failed run heals on the next one.
     *
     * @param  array<int, array{title: string, rows: array<int, array<int, mixed>>}>  $tables
     */
    private function push(GoogleSheetsClient $client, array $tables): void
    {
        $existing = $client->sheetIds();
        $wanted = [];

        foreach ($tables as $table) {
            $wanted[] = $table['title'];

            if (! array_key_exists($table['title'], $existing)) {
                $client->addSheet($table['title']);
            }

            $client->clear($table['title']);
            $client->write($table['title'], $table['rows']);
        }

        foreach ($existing as $title => $sheetId) {
            if (! in_array($title, $wanted, true)) {
                $client->deleteSheet($sheetId);
            }
        }
    }
}
