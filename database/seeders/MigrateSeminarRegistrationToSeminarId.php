<?php

namespace Database\Seeders;

use App\Models\Seminar;
use App\Models\SeminarRegistration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class MigrateSeminarRegistrationToSeminarId extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Migrating seminar registrations from selected_seminar (name) to seminar_id...');

        $registrations = SeminarRegistration::whereNull('seminar_id')
            ->whereNotNull('selected_seminar')
            ->get();

        $migrated = 0;
        $notFound = 0;
        $errors = 0;

        foreach ($registrations as $registration) {
            try {
                $seminar = null;
                $selectedValue = $registration->selected_seminar;

                // Try exact name match first
                $seminar = Seminar::where('name', $selectedValue)->first();

                // Try code match
                if (! $seminar) {
                    $seminar = Seminar::where('code', $selectedValue)->first();
                }

                // Try matching "Name (Label)" format - extract the name part
                if (! $seminar && preg_match('/^([^(]+)/', $selectedValue, $matches)) {
                    $extractedName = trim($matches[1]);
                    $seminar = Seminar::where('name', $extractedName)->first();
                }

                // Try partial name match as last resort
                if (! $seminar) {
                    $seminar = Seminar::where('name', 'like', "%{$selectedValue}%")->first();
                }

                if ($seminar) {
                    $registration->update(['seminar_id' => $seminar->id]);
                    $migrated++;
                    $this->command->info("✓ Migrated: {$registration->registration_code} -> {$seminar->name} (ID: {$seminar->id})");
                } else {
                    $notFound++;
                    $this->command->warn("✗ Seminar not found: '{$selectedValue}' for registration {$registration->registration_code}");
                    Log::warning('Seminar not found during migration', [
                        'registration_code' => $registration->registration_code,
                        'selected_seminar' => $selectedValue,
                    ]);
                }
            } catch (\Exception $e) {
                $errors++;
                $this->command->error("✗ Error migrating {$registration->registration_code}: {$e->getMessage()}");
                Log::error('Seminar migration error', [
                    'registration_code' => $registration->registration_code,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->command->newLine();
        $this->command->info('Migration Summary:');
        $this->command->info("✓ Migrated: {$migrated}");
        $this->command->warn("✗ Not Found: {$notFound}");
        $this->command->error("✗ Errors: {$errors}");
        $this->command->newLine();

        if ($notFound > 0 || $errors > 0) {
            $this->command->warn('Some registrations could not be migrated. Check the logs for details.');
        } else {
            $this->command->info('All registrations migrated successfully!');
        }
    }
}
