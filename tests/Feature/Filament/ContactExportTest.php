<?php

use App\Filament\Resources\HandsOnRegistrations\Actions\SaveContactAction as HandsOnSaveContactAction;
use App\Filament\Resources\HandsOnRegistrations\Pages\ListHandsOnRegistrations;
use App\Filament\Resources\HandsOnRegistrations\Pages\ViewHandsOnRegistration;
use App\Filament\Resources\SeminarRegistrations\Pages\ListSeminarRegistrations;
use App\Filament\Resources\SeminarRegistrations\Pages\ViewSeminarRegistration;
use App\Models\Country;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use App\Models\User;
use App\Services\VCard;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    actingAs($user);

    $this->indonesia = Country::factory()->indonesia()->create();
});

function contactFor(string $name, string $phone, ?string $email = null): array
{
    return ['name' => $name, 'phone' => $phone, 'email' => $email];
}

it('writes a vcard 3.0 card for a single contact', function () {
    $vcard = VCard::build([contactFor('drg. Ani Wijaya', '+6281200000001', 'ani@example.com')]);

    expect($vcard)->toBe(
        "BEGIN:VCARD\r\n"
        ."VERSION:3.0\r\n"
        ."N:;drg. Ani Wijaya;;;\r\n"
        ."FN:drg. Ani Wijaya\r\n"
        ."TEL;TYPE=CELL:+6281200000001\r\n"
        ."EMAIL;TYPE=INTERNET:ani@example.com\r\n"
        ."END:VCARD\r\n"
    );
});

it('omits the email line when a contact has no email address', function () {
    $vcard = VCard::build([contactFor('drg. Budi', '081200000002')]);

    expect($vcard)->toContain("TEL;TYPE=CELL:081200000002\r\n")
        ->not->toContain('EMAIL');
});

it('escapes separators that would otherwise break the vcard structure', function () {
    $vcard = VCard::build([contactFor('Ani; Wijaya, drg.', '081200000003')]);

    expect($vcard)->toContain("FN:Ani\; Wijaya\, drg.\r\n");
});

it('collapses duplicate phone numbers so one participant is saved once', function () {
    $contacts = VCard::dedupe([
        contactFor('drg. Ani', '0812-0000-0004'),
        contactFor('drg. Ani', '081200000004'),
        contactFor('drg. Budi', '081200000005'),
        contactFor('No Phone', ''),
    ]);

    expect($contacts)->toHaveCount(2)
        ->and($contacts[0]['phone'])->toBe('0812-0000-0004')
        ->and($contacts[1]['phone'])->toBe('081200000005');
});

it('offers save contact only for seminar registrations with a phone number', function () {
    $withPhone = SeminarRegistration::factory()->create(['phone' => '081200000006', 'country_id' => $this->indonesia->id]);
    $withoutPhone = SeminarRegistration::factory()->create(['phone' => '', 'country_id' => $this->indonesia->id]);

    livewire(ListSeminarRegistrations::class)
        ->assertOk()
        ->assertTableActionVisible('saveContact', $withPhone)
        ->assertTableActionHidden('saveContact', $withoutPhone);
});

it('downloads a single contact card for a seminar participant', function () {
    $registration = SeminarRegistration::factory()->create([
        'name_license' => 'drg. Ani Wijaya',
        'phone' => '081200000007',
        'email' => 'ani@example.com',
        'country_id' => $this->indonesia->id,
    ]);

    $expected = VCard::build([contactFor('drg. Ani Wijaya', '+6281200000007', 'ani@example.com')]);

    livewire(ListSeminarRegistrations::class)
        ->callTableAction('saveContact', $registration)
        ->assertFileDownloaded('drg-ani-wijaya.vcf', $expected);
});

it('normalises an international participant number using their own country code', function () {
    $singapore = Country::factory()->international()->create([
        'name' => 'Singapore',
        'code' => 'SG',
        'phone_code' => '+65',
    ]);

    $registration = SeminarRegistration::factory()->create([
        'name_license' => 'Dr. Wei Ling',
        'phone' => '91234567',
        'country_id' => $singapore->id,
    ]);

    $expected = VCard::build([contactFor('Dr. Wei Ling', '+6591234567', $registration->email)]);

    livewire(ListSeminarRegistrations::class)
        ->callTableAction('saveContact', $registration)
        ->assertFileDownloaded(content: $expected);
});

it('normalises a stored indonesian number into the saved contact card', function () {
    $registration = SeminarRegistration::factory()->create([
        'name_license' => 'drg. Fajar',
        'phone' => '0812-9999-8888',
        'country_id' => $this->indonesia->id,
    ]);

    $expected = VCard::build([contactFor('drg. Fajar', '+6281299998888', $registration->email)]);

    livewire(ListSeminarRegistrations::class)
        ->callTableAction('saveContact', $registration)
        ->assertFileDownloaded(content: $expected);
});

it('offers save contact from the seminar view page', function () {
    $registration = SeminarRegistration::factory()->create(['phone' => '081200000008']);

    livewire(ViewSeminarRegistration::class, ['record' => $registration->getRouteKey()])
        ->assertOk()
        ->assertActionVisible('saveContact');
});

it('inherits the seminar participant identity for a hands-on registration', function () {
    $seminar = SeminarRegistration::factory()->create([
        'name_license' => 'drg. Citra',
        'phone' => '081200000009',
        'email' => 'citra@example.com',
        'country_id' => $this->indonesia->id,
    ]);

    $handsOn = HandsOnRegistration::factory()->create([
        'seminar_registration_id' => $seminar->id,
        'phone' => null,
    ]);

    expect(HandsOnSaveContactAction::contactFor($handsOn))->toBe([
        'name' => 'drg. Citra',
        'phone' => '+6281200000009',
        'email' => 'citra@example.com',
    ]);
});

it('downloads a single contact card for a hands-on participant', function () {
    $seminar = SeminarRegistration::factory()->create([
        'name_license' => 'drg. Citra',
        'phone' => '081200000010',
        'country_id' => $this->indonesia->id,
    ]);

    $handsOn = HandsOnRegistration::factory()->create([
        'seminar_registration_id' => $seminar->id,
        'phone' => null,
    ]);

    $expected = VCard::build([contactFor('drg. Citra', '+6281200000010', $seminar->email)]);

    livewire(ListHandsOnRegistrations::class)
        ->callTableAction('saveContact', $handsOn)
        ->assertFileDownloaded('drg-citra.vcf', $expected);
});

it('exports selected hands-on participants as one contact per phone number', function () {
    $seminar = SeminarRegistration::factory()->create([
        'name_license' => 'drg. Dewi',
        'phone' => '081200000011',
        'country_id' => $this->indonesia->id,
    ]);

    $registrations = HandsOnRegistration::factory()->count(2)->create([
        'seminar_registration_id' => $seminar->id,
        'phone' => null,
    ]);

    $expected = VCard::build([contactFor('drg. Dewi', '+6281200000011', $seminar->email)]);

    livewire(ListHandsOnRegistrations::class)
        ->callTableBulkAction('exportContacts', $registrations->pluck('id')->all())
        ->assertFileDownloaded(content: $expected);
});

it('exports every seminar participant with a phone number from the list header', function () {
    $withPhone = SeminarRegistration::factory()->create([
        'name_license' => 'drg. Eka',
        'phone' => '081200000012',
        'country_id' => $this->indonesia->id,
    ]);
    SeminarRegistration::factory()->create(['phone' => '', 'country_id' => $this->indonesia->id]);

    $expected = VCard::build([contactFor('drg. Eka', '+6281200000012', $withPhone->email)]);

    livewire(ListSeminarRegistrations::class)
        ->callAction('exportAllContacts')
        ->assertFileDownloaded(content: $expected);
});

it('warns instead of downloading when no selected participant has a phone number', function () {
    $registrations = SeminarRegistration::factory()->count(2)->create(['phone' => '', 'country_id' => $this->indonesia->id]);

    livewire(ListSeminarRegistrations::class)
        ->callTableBulkAction('exportContacts', $registrations->pluck('id')->all())
        ->assertNotified(__('filament.contacts.none_selected'));
});

it('hides the export and save-contact actions from the Admin role', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    actingAs($admin);

    $seminar = SeminarRegistration::factory()->create(['phone' => '081200000020']);
    $handsOn = HandsOnRegistration::factory()->create(['phone' => '081200000021']);

    livewire(ListSeminarRegistrations::class)
        ->assertActionHidden('export')
        ->assertActionHidden('exportAllContacts')
        ->assertTableBulkActionHidden('exportContacts')
        ->assertTableActionHidden('saveContact', $seminar);

    livewire(ListHandsOnRegistrations::class)
        ->assertActionHidden('export')
        ->assertActionHidden('exportAllContacts')
        ->assertTableBulkActionHidden('exportContacts')
        ->assertTableActionHidden('saveContact', $handsOn);

    livewire(ViewSeminarRegistration::class, ['record' => $seminar->getRouteKey()])
        ->assertActionHidden('saveContact');

    livewire(ViewHandsOnRegistration::class, ['record' => $handsOn->getRouteKey()])
        ->assertActionHidden('saveContact');
});
