<?php

namespace App\Filament\Resources\Permissions\Pages;

use App\Filament\Resources\Permissions\PermissionResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission as SpatiePermission;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateFromPolicies')
                ->label('Generate from Policies')
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Generate Permissions from Policies')
                ->modalDescription('This will scan all policy files and create any missing permissions.')
                ->modalSubmitActionLabel('Generate')
                ->action(function (): void {
                    $this->generatePermissionsFromPolicies();
                }),
            CreateAction::make(),
        ];
    }

    private function generatePermissionsFromPolicies(): void
    {
        $policyPath = app_path('Policies');
        $policyFiles = File::files($policyPath);
        $createdCount = 0;
        $existingCount = 0;

        foreach ($policyFiles as $file) {
            $content = File::get($file->getPathname());
            $permissions = $this->extractPermissionsFromPolicy($content);

            foreach ($permissions as $permissionName) {
                if (! SpatiePermission::where('name', $permissionName)->exists()) {
                    SpatiePermission::create(['name' => $permissionName]);
                    $createdCount++;
                } else {
                    $existingCount++;
                }
            }
        }

        Notification::make()
            ->title('Permissions Generated')
            ->body("Created {$createdCount} new permissions. {$existingCount} permissions already exist.")
            ->success()
            ->send();
    }

    private function extractPermissionsFromPolicy(string $content): array
    {
        $permissions = [];

        // Match patterns like: $user->can('view hands ons') or $user->can("view hands ons")
        preg_match_all('/\$user->can\([\'\"]([^\'\"]+)[\'\"]\)/', $content, $matches);

        if (! empty($matches[1])) {
            $permissions = array_unique($matches[1]);
        }

        return $permissions;
    }
}
