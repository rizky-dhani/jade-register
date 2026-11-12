<?php

use App\Filament\Pages\DatabaseBackups;
use App\Filament\Pages\HandsOnRegistrationReport;
use App\Filament\Pages\SeminarRegistrationReport;
use App\Filament\Resources\Addons\AddonResource;
use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Resources\Countries\CountryResource;
use App\Filament\Resources\DigitalWorkshopRegistrationIntents\DigitalWorkshopRegistrationIntentResource;
use App\Filament\Resources\DigitalWorkshopRegistrations\DigitalWorkshopRegistrationResource;
use App\Filament\Resources\DigitalWorkshops\DigitalWorkshopResource;
use App\Filament\Resources\HandsOnRegistrations\HandsOnRegistrationResource;
use App\Filament\Resources\HandsOns\HandsOnResource;
use App\Filament\Resources\Permissions\PermissionResource;
use App\Filament\Resources\PosterEvaluations\PosterEvaluationResource;
use App\Filament\Resources\PosterSubmissions\PosterSubmissionResource;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\SeminarRegistrations\SeminarRegistrationResource;
use App\Filament\Resources\Seminars\SeminarResource;
use App\Filament\Resources\Settings\SettingResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\Visitors\VisitorResource;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    actingAs($admin);
});

/**
 * The Admin role is a desk role: it may only reach the attendance desk and the
 * three registration/visitor lists. Everything else is locked.
 */
it('lets an Admin reach the allowed pages', function () {
    get(AttendanceResource::getUrl('index'))->assertOk();
    get(AttendanceResource::getUrl('visitor'))->assertOk();
    get(VisitorResource::getUrl('index'))->assertOk();
    get(SeminarRegistrationResource::getUrl('index'))->assertOk();
    get(HandsOnRegistrationResource::getUrl('index'))->assertOk();
});

it('locks every other resource for an Admin', function (string $resource) {
    get($resource::getUrl('index'))->assertForbidden();
})->with([
    AddonResource::class,
    CountryResource::class,
    DigitalWorkshopRegistrationIntentResource::class,
    DigitalWorkshopRegistrationResource::class,
    DigitalWorkshopResource::class,
    HandsOnResource::class,
    PermissionResource::class,
    PosterEvaluationResource::class,
    PosterSubmissionResource::class,
    RoleResource::class,
    SeminarResource::class,
    SettingResource::class,
    UserResource::class,
]);

it('locks the lunch log for an Admin', function () {
    get(AttendanceResource::getUrl('lunch'))->assertForbidden();
});

it('locks the reporting pages and database backups for an Admin', function () {
    get(HandsOnRegistrationReport::getUrl())->assertForbidden();
    get(SeminarRegistrationReport::getUrl())->assertForbidden();
    get(DatabaseBackups::getUrl())->assertForbidden();
});
