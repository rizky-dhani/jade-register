<?php

use App\Mail\HandsOnRegistrationConfirmation;
use App\Mail\SeminarRegistrationConfirmation;
use App\Models\Country;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

const ACTIVE_GROUP = 'https://chat.whatsapp.com/FOUtwzgjBodABp1TdsEQcH?s=cl&p=a&mlu=4&ilr=4';
const DEAD_GROUP_EMAIL = 'https://chat.whatsapp.com/H6ORdZnLmcM1uhgn0RIh40';
const DEAD_GROUP_WEB = 'https://chat.whatsapp.com/KtELLi4Q22VHqJWFavOwhQ';

/**
 * Blade escapes `&` to `&amp;` inside href attributes.
 */
function hrefFor(string $url): string
{
    return str_replace('&', '&amp;', $url);
}

function indonesianRegistration(): SeminarRegistration
{
    $country = Country::factory()->indonesia()->create();

    return SeminarRegistration::factory()->create([
        'country_id' => $country->id,
        'language' => 'id',
    ]);
}

function renderSeminarEmail(SeminarRegistration $registration): string
{
    return (new SeminarRegistrationConfirmation($registration))->locale('id')->render();
}

beforeEach(function () {
    Setting::create([
        'key' => 'whatsapp_group_url',
        'label' => 'WhatsApp Group URL',
        'type' => 'string',
        'value' => ACTIVE_GROUP,
    ]);
});

it('renders the configured whatsapp group url in the seminar confirmation email', function () {
    $html = renderSeminarEmail(indonesianRegistration());

    expect($html)->toContain(hrefFor(ACTIVE_GROUP))
        ->and($html)->not->toContain(DEAD_GROUP_EMAIL);
});

it('renders the configured whatsapp group url in the hands-on confirmation email', function () {
    $registration = indonesianRegistration();

    $handsOnRegistration = HandsOnRegistration::create([
        'seminar_registration_id' => $registration->id,
        'hands_on_id' => HandsOn::factory()->create()->id,
        'registration_type' => 'online',
        'payment_status' => 'verified',
        'country_id' => $registration->country_id,
        'language' => 'id',
    ]);

    $html = (new HandsOnRegistrationConfirmation($handsOnRegistration))->locale('id')->render();

    expect($html)->toContain(hrefFor(ACTIVE_GROUP))
        ->and($html)->not->toContain(DEAD_GROUP_EMAIL);
});

it('uses an updated setting value instead of the hardcoded link', function () {
    Setting::where('key', 'whatsapp_group_url')->update(['value' => 'https://chat.whatsapp.com/NEWGROUPTOKEN']);

    $html = renderSeminarEmail(indonesianRegistration());

    expect($html)->toContain('https://chat.whatsapp.com/NEWGROUPTOKEN')
        ->and($html)->not->toContain('FOUtwzgjBodABp1TdsEQcH');
});

it('falls back to the config default when the setting row is missing', function () {
    Setting::where('key', 'whatsapp_group_url')->delete();

    $html = renderSeminarEmail(indonesianRegistration());

    expect($html)->toContain(hrefFor(config('settings.whatsapp_group_url.default')));
});

it('hides the whatsapp group button when the url is empty', function () {
    Setting::where('key', 'whatsapp_group_url')->update(['value' => '']);

    $html = renderSeminarEmail(indonesianRegistration());

    expect($html)->not->toContain('chat.whatsapp.com');
});

it('renders the group section heading for indonesian participants', function () {
    $html = renderSeminarEmail(indonesianRegistration());

    expect($html)->toContain(trans('seminar.email_whatsapp_group_title', [], 'id'));
});

it('omits the group section entirely for international participants', function () {
    $country = Country::factory()->international()->create();

    $registration = SeminarRegistration::factory()->create([
        'country_id' => $country->id,
        'language' => 'en',
    ]);

    $html = (new SeminarRegistrationConfirmation($registration))->locale('en')->render();

    expect($html)->not->toContain('chat.whatsapp.com')
        ->and($html)->not->toContain(trans('seminar.email_whatsapp_group_title', [], 'en'));
});

it('no longer ships the stale dead group links in any view', function () {
    $views = glob(resource_path('views/emails/*.blade.php'));
    $views = array_merge($views, glob(resource_path('views/livewire/*-registration-success.blade.php')));

    foreach ($views as $view) {
        $contents = file_get_contents($view);

        expect($contents)->not->toContain(DEAD_GROUP_EMAIL)
            ->and($contents)->not->toContain(DEAD_GROUP_WEB);
    }
});
it('exposes the group url as a mailable property resolved from the setting', function () {
    $mail = new SeminarRegistrationConfirmation(indonesianRegistration());

    expect($mail->whatsappGroupUrl)->toBe(ACTIVE_GROUP);
});

it('falls back to the config default on the mailable when the setting row is missing', function () {
    Setting::where('key', 'whatsapp_group_url')->delete();

    $mail = new SeminarRegistrationConfirmation(indonesianRegistration());

    expect($mail->whatsappGroupUrl)->toBe(config('settings.whatsapp_group_url.default'));
});

it('passes an updated setting through to the mailable property', function () {
    Setting::where('key', 'whatsapp_group_url')->update(['value' => 'https://chat.whatsapp.com/MAILABLENEW']);

    $mail = new SeminarRegistrationConfirmation(indonesianRegistration());

    expect($mail->whatsappGroupUrl)->toBe('https://chat.whatsapp.com/MAILABLENEW');
});

it('resolves the group url in the surfaces that do not go through the mailable', function () {
    $inlineSurfaces = [
        resource_path('views/emails/hands-on-registration-confirmation.blade.php'),
        resource_path('views/livewire/seminar-registration-success.blade.php'),
        resource_path('views/livewire/hands-on-registration-success.blade.php'),
    ];

    foreach ($inlineSurfaces as $view) {
        expect(file_get_contents($view))->toContain("Setting::get('whatsapp_group_url'");
    }
});

it('no longer resolves the setting inline inside the seminar confirmation email view', function () {
    $view = file_get_contents(resource_path('views/emails/seminar-registration-confirmation.blade.php'));

    expect($view)->not->toContain("Setting::get('whatsapp_group_url'")
        ->and($view)->toContain('$whatsappGroupUrl');
});
