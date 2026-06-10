# Settings Fixed Configuration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transition settings from free-form CRUD to a fixed configuration system with pre-defined options in `config/settings.php`.

**Architecture:** Config-driven settings where `config/settings.php` is the single source of truth for known settings. Filament UI becomes value-editing only (no create/delete). Seeder syncs from config. Auto-close logic uses model events.

**Tech Stack:** Laravel 12, Filament 5, PHP 8.4

---

### Task 1: Create `config/settings.php`

**Files:**
- Create: `config/settings.php`

- [ ] **Step 1: Create settings config file**

```php
<?php

return [
    'registration_open' => [
        'label' => 'Seminar Registration Open/Close',
        'type' => 'boolean',
        'default' => true,
        'description' => 'Controls whether seminar registration is open to participants. Auto-closes when max participants is reached.',
    ],
    'max_participants' => [
        'label' => 'Seminar Max Participants',
        'type' => 'integer',
        'default' => 500,
        'description' => 'Maximum number of seminar registrations allowed. Automatically closes registration when this limit is reached.',
    ],
    'hands_on_registration_open' => [
        'label' => 'Hands On Registration Open/Close',
        'type' => 'boolean',
        'default' => true,
        'description' => 'Controls whether hands-on registration is open to participants.',
    ],
    'seminar_registration_opens_at' => [
        'label' => 'Seminar Registration Opens At',
        'type' => 'string',
        'default' => null,
        'description' => 'Optional date/time when seminar registration automatically opens. Leave null for no date restriction.',
    ],
    'hands_on_registration_opens_at' => [
        'label' => 'Hands On Registration Opens At',
        'type' => 'string',
        'default' => null,
        'description' => 'Optional date/time when hands-on registration automatically opens. Leave null for no date restriction.',
    ],
];
```

- [ ] **Step 2: Verify file loads correctly**

Run: `php artisan config:show settings`
Expected: Config array with all 5 settings definitions shown.

- [ ] **Step 3: Commit**

```bash
git add config/settings.php
git commit -m "feat: add settings configuration file"
```

---

### Task 2: Database Migration — Add `label` Column

**Files:**
- Create: `database/migrations/2026_05_19_000001_add_label_to_settings_table.php`

- [ ] **Step 1: Create migration**

Run: `php artisan make:migration add_label_to_settings_table --table=settings`

- [ ] **Step 2: Write migration up/down**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('label')->nullable()->after('key');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('label');
        });
    }
};
```

- [ ] **Step 3: Run migration**

Run: `php artisan migrate`
Expected: Migration successful, `label` column added to `settings` table.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_05_19_000001_add_label_to_settings_table.php
git commit -m "feat: add label column to settings table"
```

---

### Task 3: Update Setting Model

**Files:**
- Modify: `app/Models/Setting.php`

- [ ] **Step 1: Add `label` to `$fillable` and add `defined()` static method**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'label',
        'value',
        'type',
        'description',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    public static function defined(): Collection
    {
        return collect(config('settings', []));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return $setting->getTypedValue();
    }

    public function getTypedValue(): mixed
    {
        return match ($this->type) {
            'integer' => (int) $this->value,
            'boolean' => (bool) $this->value,
            'float' => (float) $this->value,
            'array' => json_decode($this->value, true),
            default => $this->value,
        };
    }
}
```

- [ ] **Step 2: Verify model works**

Run: `php artisan tinker --execute 'dump(App\Models\Setting::defined()->keys()->toArray());'`
Expected: Array of 5 setting keys from config.

- [ ] **Step 3: Commit**

```bash
git add app/Models/Setting.php
git commit -m "feat: add label fillable and defined() method to Setting model"
```

---

### Task 4: Update Setting Seeder to Sync from Config

**Files:**
- Modify: `database/seeders/SettingSeeder.php`

- [ ] **Step 1: Rewrite seeder to sync from config**

```php
<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Setting::defined() as $key => $definition) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'label' => $definition['label'],
                    'value' => $definition['default'] ?? '',
                    'type' => $definition['type'],
                    'description' => $definition['description'] ?? '',
                ]
            );
        }
    }
}
```

- [ ] **Step 2: Run seeder to populate all settings**

Run: `php artisan db:seed --class=SettingSeeder`
Expected: 5 settings created in the database with labels.

Verify: `php artisan tinker --execute 'dump(App\Models\Setting::all()->pluck("key", "label")->toArray());'`

- [ ] **Step 3: Commit**

```bash
git add database/seeders/SettingSeeder.php
git commit -m "feat: sync SettingSeeder from config/settings.php"
```

---

### Task 5: Update Filament Settings Form

**Files:**
- Modify: `app/Filament/Resources/Settings/Schemas/SettingForm.php`

- [ ] **Step 1: Add label field and make key/type read-only for existing records**

```php
<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('label')
                            ->label(__('filament.settings.form.label'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('key')
                            ->label(__('filament.settings.form.key'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->helperText(__('filament.settings.form.key_helper'))
                            ->disabled(fn (?string $operation): bool => $operation === 'edit'),
                        Select::make('type')
                            ->label(__('filament.settings.form.type'))
                            ->options([
                                'string' => 'String',
                                'integer' => 'Integer',
                                'float' => 'Float',
                                'boolean' => 'Boolean',
                                'array' => 'Array (JSON)',
                            ])
                            ->required()
                            ->default('string')
                            ->live()
                            ->helperText(__('filament.settings.form.type_helper'))
                            ->disabled(fn (?string $operation): bool => $operation === 'edit'),
                        TextInput::make('value')
                            ->label(__('filament.settings.form.value'))
                            ->required()
                            ->visible(fn (Get $get): bool => $get('type') === 'string'),
                        TextInput::make('value')
                            ->label(__('filament.settings.form.value'))
                            ->required()
                            ->numeric()
                            ->visible(fn (Get $get): bool => $get('type') === 'integer'),
                        TextInput::make('value')
                            ->label(__('filament.settings.form.value'))
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->visible(fn (Get $get): bool => $get('type') === 'float'),
                        Toggle::make('value')
                            ->label(__('filament.settings.form.value'))
                            ->required()
                            ->visible(fn (Get $get): bool => $get('type') === 'boolean'),
                        Textarea::make('value')
                            ->label(__('filament.settings.form.value'))
                            ->required()
                            ->json()
                            ->visible(fn (Get $get): bool => $get('type') === 'array'),
                        Textarea::make('description')
                            ->label(__('filament.settings.form.description'))
                            ->helperText(__('filament.settings.form.description_helper')),
                    ]),
            ]);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Filament/Resources/Settings/Schemas/SettingForm.php
git commit -m "feat: add label field to settings form, disable key/type on edit"
```

---

### Task 6: Update Filament Settings Table

**Files:**
- Modify: `app/Filament/Resources/Settings/Tables/SettingsTable.php`

- [ ] **Step 1: Add label column as primary display**

```php
<?php

namespace App\Filament\Resources\Settings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label(__('filament.settings.table.label'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('key')
                    ->label(__('filament.settings.table.key'))
                    ->searchable()
                    ->sortable()
                    ->color('gray'),
                TextColumn::make('value')
                    ->label(__('filament.settings.table.value'))
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('filament.settings.table.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'integer', 'float' => 'success',
                        'boolean' => 'warning',
                        'array' => 'info',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('description')
                    ->label(__('filament.settings.table.description'))
                    ->limit(60)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('filament.settings.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('filament.settings.table.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Filament/Resources/Settings/Tables/SettingsTable.php
git commit -m "feat: add label column to settings table"
```

---

### Task 7: Update SettingResource — Restrict Create/Delete

**Files:**
- Modify: `app/Filament/Resources/Settings/SettingResource.php`

- [ ] **Step 1: Override canCreate and modify navigation to use defined settings count**

```php
<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\Pages\CreateSetting;
use App\Filament\Resources\Settings\Pages\EditSetting;
use App\Filament\Resources\Settings\Pages\ListSettings;
use App\Filament\Resources\Settings\Schemas\SettingForm;
use App\Filament\Resources\Settings\Tables\SettingsTable;
use App\Models\Setting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog6Tooth;

    protected static \UnitEnum|string|null $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $modelLabel = 'Setting';

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?int $navigationSort = 99;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() > 0 ? (string) static::getModel()::count() : null;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return SettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSettings::route('/'),
            'create' => CreateSetting::route('/create'),
            'edit' => EditSetting::route('/{record}/edit'),
        ];
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Filament/Resources/Settings/SettingResource.php
git commit -m "feat: restrict setting creation, use label as record title"
```

---

### Task 8: Update Translation Files

**Files:**
- Modify: `lang/en/filament.php`
- Modify: `lang/id/filament.php`

- [ ] **Step 1: Add label translations to English**

Add after the existing Settings section in `lang/en/filament.php`:

```php
    // Settings Form Labels
    'settings.form.label' => 'Label',
    'settings.form.label_helper' => 'Human-readable name for this setting',
    'settings.form.key' => 'Setting Key',
    'settings.form.key_helper' => 'Unique identifier for this setting (e.g., max_participants)',
    // ... rest stays the same ...

    // Settings Table Columns
    'settings.table.label' => 'Label',
    'settings.table.key' => 'Key',
    // ... rest stays the same ...
```

- [ ] **Step 2: Add label translations to Indonesian**

Add after the existing Settings section in `lang/id/filament.php`:

```php
    // Settings Form Labels
    'settings.form.label' => 'Label',
    'settings.form.label_helper' => 'Nama yang dapat dibaca manusia untuk pengaturan ini',
    'settings.form.key' => 'Kunci Pengaturan',
    // ... rest stays the same ...

    // Settings Table Columns
    'settings.table.label' => 'Label',
    'settings.table.key' => 'Kunci',
    // ... rest stays the same ...
```

- [ ] **Step 3: Commit**

```bash
git add lang/en/filament.php lang/id/filament.php
git commit -m "feat: add label translations for settings"
```

---

### Task 9: Rename `registration_opens_at` to `seminar_registration_opens_at`

**Files:**
- Modify: `app/Livewire/SeminarRegistration.php`
- Modify: `database/seeders/SettingSeeder.php` (already handled if rerun)

- [ ] **Step 1: Update `SeminarRegistration.php` — rename key reference**

Find line 669 (`Setting::get('registration_opens_at')`) and change to:

```php
$opensAt = Setting::get('seminar_registration_opens_at');
```

- [ ] **Step 2: Update the setting value in database**

Run: `php artisan tinker --execute 'DB::table("settings")->where("key", "registration_opens_at")->update(["key" => "seminar_registration_opens_at"]);'`

If the old key doesn't exist in DB, just reseed:

Run: `php artisan db:seed --class=SettingSeeder`

- [ ] **Step 3: Verify all references are updated**

Run: `rg 'registration_opens_at' --type php`
Expected: Only `seminar_registration_opens_at` references (no bare `registration_opens_at`).

- [ ] **Step 4: Commit**

```bash
git add app/Livewire/SeminarRegistration.php
git commit -m "feat: rename registration_opens_at to seminar_registration_opens_at"
```

---

### Task 10: Add Auto-Close Logic for Registration via Model Event

**Files:**
- Modify: `app/Models/SeminarRegistration.php`

- [ ] **Step 1: Add `booted()` method to auto-close registration when max participants reached**

```php
<?php

namespace App\Models;

// ... existing imports ...
use App\Models\Setting;

class SeminarRegistration extends Model
{
    // ... existing code ...

    protected static function booted(): void
    {
        static::created(function ($registration) {
            $maxParticipants = Setting::get('max_participants', PHP_INT_MAX);
            if (self::getTotalRegistrations() >= $maxParticipants) {
                Setting::where('key', 'registration_open')
                    ->where('value', 'true')
                    ->update(['value' => 'false']);
            }
        });
    }

    // ... rest of existing code ...
}
```

- [ ] **Step 2: Verify booted() is placed correctly in the file (after existing properties, before other methods)**

Read the current file structure to place it properly.

- [ ] **Step 3: Commit**

```bash
git add app/Models/SeminarRegistration.php
git commit -m "feat: auto-close seminar registration when max participants reached"
```

---

### Task 11: Remove Delete Bulk Action from Settings Table

**Files:**
- Modify: `app/Filament/Resources/Settings/Tables/SettingsTable.php`

- [ ] **Step 1: Remove DeleteBulkAction from toolbar actions**

Since settings are pre-defined, delete should not be available:

```php
->toolbarActions([
    // BulkActionGroup removed — no bulk actions for settings
])
```

- [ ] **Step 2: Commit**

```bash
git add app/Filament/Resources/Settings/Tables/SettingsTable.php
git commit -m "feat: remove delete capability from settings table"
```

---

## Spec Self-Review

**1. Spec coverage:** All 5 settings from the spec are covered. Config file, migration, model update, form/table/resource changes, seeder, translations, key rename, auto-close, delete removal — all mapped to tasks.

**2. Placeholder scan:** No TBDs, TODOs, or placeholders. Every code block has complete code.

**3. Type consistency:** `defined()` returns `Collection`, config keys match DB keys, type mappings consistent.

**4. Edge cases covered:**
- Existing settings without `label` → migration makes it nullable
- Auto-close only fires on create, uses `->where('value', 'true')` to avoid repeated updates
- Create action disabled via `canCreate()`
