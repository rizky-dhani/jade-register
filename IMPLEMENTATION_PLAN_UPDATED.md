# Dental Exhibition Registration System - Implementation Plan (UPDATED)

## Project Overview
A comprehensive registration system for Jakarta Dental Exhibition 2026 featuring:
- **Visitor Registration** (Free entrance tracking)
- **Seminar Registration** (Paid, with payment verification)
  - **Online Registration** - For pre-event registration (remote users)
  - **Offline Registration** - For on-site registration (venue participants)
- **Admin Panel** (Filament-powered management system)

**Event Dates:** 13-15 November 2026  
**Event Logo:** `/public/assets/images/Jade_Logo.webp`  
**Registration Access:** Public (no authentication required)  
**Detection Method:** Browser Geolocation API

---

## Key Feature: Smart Registration Type Detection

The system automatically detects if a user is on-site at the venue using browser geolocation:

1. **On-site users** (coordinates within venue radius):
   - See OFFLINE pricing tiers
   - Auto-set registration_type = 'offline'
   
2. **Remote users** (outside venue or location denied):
   - See ONLINE pricing tiers
   - Auto-set registration_type = 'online'

3. **Manual Override** (optional):
   - User can toggle between Online/Offline if detection fails

---

## Phase 0: Dependencies & Configuration

### 0.1 Install Spatie Laravel Permission
```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="config"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"
```

### 0.2 Configure Email Settings
Update `.env` with mail server credentials:
```
MAIL_FROM_ADDRESS="noreply@jakartadentalexhibitions.id"
MAIL_FROM_NAME="Jakarta Dental Exhibition"
```

---

## Phase 1: Database Architecture

### 1.1 Migrations

#### `create_countries_table`
- `id` (bigint, primary key)
- `name` (string, unique) - Full country name
- `code` (string, unique, 3 chars) - ISO 3166-1 alpha-3 code
- `is_local` (boolean, default false) - Indonesia = true
- `phone_code` (string, nullable) - e.g., "+62"
- `created_at`, `updated_at`

#### `create_visitors_table`
- `id` (bigint, primary key)
- `name` (string)
- `email` (string, unique)
- `phone` (string)
- `affiliation` (string, nullable) - Institution/Company name
- `profession` (string)
- `preferred_visit_date` (date) - Must be between 13-15 Nov 2026
- `marketing_source` (string, nullable)
- `created_at`, `updated_at`

#### `create_seminar_registrations_table` (UPDATED)
- `id` (bigint, primary key)
- `registration_code` (string, unique) - Format: SEM-2026-0001
- `name` (string)
- `email` (string, unique)
- `phone` (string)
- `affiliation` (string, nullable)
- `country_id` (foreign key -> countries.id)
- `registration_type` (enum: 'online', 'offline') - **NEW FIELD**
- `pricing_tier` (enum - see PricingTier enum below)
- `amount` (unsignedBigInteger) - Amount in Rupiah
- `payment_status` (enum: 'pending', 'verified', 'rejected')
- `payment_proof_path` (string, nullable)
- `rejection_reason` (text, nullable)
- `verified_by` (foreign key nullable -> users.id)
- `verified_at` (timestamp, nullable)
- `created_at`, `updated_at`

#### `create_settings_table` (UPDATED)
- `id` (bigint, primary key)
- `key` (string, unique) - Setting identifier
- `value` (text) - Setting value (JSON or plain text)
- `created_at`, `updated_at`

**Settings to include:**
- `bank_account_name` (string)
- `bank_account_number` (string)
- `bank_name` (string)
- `payment_instructions` (text)
- `event_terms_conditions` (text, nullable)
- `venue_name` (string) - e.g., "Jakarta Convention Center"
- `venue_latitude` (decimal 10, 8) - **NEW** e.g., -6.2088
- `venue_longitude` (decimal 11, 8) - **NEW** e.g., 106.8456
- `venue_detection_radius` (integer) - **NEW** Radius in meters (default: 500)
- `venue_address` (text) - Full venue address

#### `create_professions_table`
- `id` (bigint, primary key)
- `name` (string, unique)
- `sort_order` (integer, default 0)
- `created_at`, `updated_at`

#### `create_marketing_sources_table`
- `id` (bigint, primary key)
- `name` (string, unique)
- `sort_order` (integer, default 0)
- `created_at`, `updated_at`

---

### 1.2 Models

#### `App\Models\Country`
```php
Relationships:
- hasMany(SeminarRegistration::class)

Scopes:
- scopeLocal() - where('is_local', true)
- scopeInternational() - where('is_local', false)

Accessors:
- getDisplayNameAttribute() - "Indonesia" or "United States"
```

#### `App\Models\Visitor`
```php
Fillable: name, email, phone, affiliation, profession, preferred_visit_date, marketing_source

Casts:
- preferred_visit_date => 'date'

Accessors:
- getFormattedVisitDateAttribute() - "Friday, 13 November 2026"
```

#### `App\Models\SeminarRegistration` (UPDATED)
```php
Relationships:
- belongsTo(Country::class)
- belongsTo(User::class, 'verified_by')

Fillable: 
- registration_code, name, email, phone, affiliation, country_id
- registration_type, pricing_tier, amount
- payment_status, payment_proof_path, rejection_reason
- verified_by, verified_at

Casts:
- amount => 'integer'
- verified_at => 'datetime'

Accessors:
- getFormattedAmountAttribute() - "IDR 600.000"
- getStatusBadgeAttribute() - Return color for badge
- getRegistrationTypeLabelAttribute() - "Online Registration" or "On-site Registration"

Methods:
- generateRegistrationCode() - Generate unique SEM-2026-XXXX
- static findByCode(string $code) - Find by registration code
- isOnline() - Check if registration_type = 'online'
- isOffline() - Check if registration_type = 'offline'
```

#### `App\Models\Setting` (UPDATED)
```php
Fillable: key, value

Casts:
- value => 'json' (if needed)

Static Methods:
- static get(string $key, mixed $default = null) - Retrieve setting value
- static set(string $key, mixed $value) - Set setting value

Convenience Methods:
- static getVenueCoordinates(): array - Returns ['lat' => ..., 'lng' => ...]
- static getVenueRadius(): int - Returns detection radius in meters
```

#### `App\Models\Profession`
```php
Fillable: name, sort_order

Scopes:
- scopeOrdered() - orderBy('sort_order')
```

#### `App\Models\MarketingSource`
```php
Fillable: name, sort_order

Scopes:
- scopeOrdered() - orderBy('sort_order')
```

---

### 1.3 Enums

#### `App\Enums\RegistrationType` (NEW)
```php
ONLINE = 'online'
OFFLINE = 'offline'

Methods:
- getLabel(): string
  - ONLINE => "Online Registration"
  - OFFLINE => "On-site Registration"
  
- getDescription(): string
  - ONLINE => "Pre-event registration via website"
  - OFFLINE => "On-site registration at the venue"
```

#### `App\Enums\PricingTier` (UPDATED - Now 6 tiers)
```php
// ONLINE PRICING TIERS
ONLINE_LOCAL_SNACK_ONLY = 'online_local_snack_only'           // IDR 600.000
ONLINE_LOCAL_SNACK_LUNCH = 'online_local_snack_lunch'         // IDR 900.000
ONLINE_INTERNATIONAL_SNACK_LUNCH = 'online_international_snack_lunch' // IDR 1.500.000

// OFFLINE PRICING TIERS
OFFLINE_LOCAL_SNACK_LUNCH_1 = 'offline_local_snack_lunch_1'   // IDR 900.000
OFFLINE_LOCAL_SNACK_LUNCH_2 = 'offline_local_snack_lunch_2'   // IDR 1.200.000
OFFLINE_INTERNATIONAL_SNACK_LUNCH = 'offline_international_snack_lunch' // IDR 2.500.000

Methods:
- getLabel(): string
  - ONLINE_LOCAL_SNACK_ONLY => "Online - Local - Snack Only"
  - ONLINE_LOCAL_SNACK_LUNCH => "Online - Local - Snack + Lunch"
  - ONLINE_INTERNATIONAL_SNACK_LUNCH => "Online - International - Snack + Lunch"
  - OFFLINE_LOCAL_SNACK_LUNCH_1 => "On-site - Local - Snack + Lunch (Tier 1)"
  - OFFLINE_LOCAL_SNACK_LUNCH_2 => "On-site - Local - Snack + Lunch (Tier 2)"
  - OFFLINE_INTERNATIONAL_SNACK_LUNCH => "On-site - International - Snack + Lunch"
  
- getPrice(): int
  - Returns 600000, 900000, 1500000, 900000, 1200000, or 2500000
  
- getFormattedPrice(): string
  - Returns "IDR 600.000", "IDR 900.000", "IDR 1.500.000", etc.
  
- getMealPreference(): string
  - Returns "Snack only" or "Snack + Lunch"
  
- isOnline(): bool - Check if tier is online pricing
- isOffline(): bool - Check if tier is offline pricing

Static Methods:
- static getOnlineTiers(): array - Return only online tiers
- static getOfflineTiers(): array - Return only offline tiers
- static getLocalTiers(RegistrationType $type): array - Return local tiers for online/offline
- static getInternationalTiers(RegistrationType $type): array - Return international tiers
```

#### `App\Enums\PaymentStatus`
```php
PENDING = 'pending'
VERIFIED = 'verified'
REJECTED = 'rejected'

Methods:
- getLabel(): string
  - "Pending Verification", "Verified", "Rejected"
  
- getColor(): string
  - 'warning', 'success', 'danger' (for Filament badges)
```

---

## Phase 2: Authorization & Roles

### 2.1 User Model Enhancement
Add `Spatie\Permission\Traits\HasRoles` trait to `App\Models\User`

### 2.2 Roles & Permissions

#### Roles
- **Super Admin** (`super-admin`)
  - Full access to all features
  - Bypass all authorization checks via `Gate::before`
  
- **Admin** (`admin`)
  - Limited access based on permissions

#### Permissions (for Admin role)
- `view visitors` - View visitor list
- `export visitors` - Export visitor data
- `view seminar registrations` - View seminar registrations
- `verify payments` - Verify payment proofs
- `reject payments` - Reject payment proofs
- `export seminar registrations` - Export seminar data
- `manage countries` - CRUD countries
- `manage settings` - Edit system settings (including venue location)
- `manage professions` - CRUD professions
- `manage marketing sources` - CRUD marketing sources

### 2.3 AppServiceProvider Configuration
```php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::before(function ($user, $ability) {
        return $user->hasRole('super-admin') ? true : null;
    });
}
```

### 2.4 Super Admin Seeder
```php
User::create([
    'name' => 'Super Admin',
    'email' => 'superadmin@jakartadentalexhibitions.id',
    'password' => Hash::make('SuJade2026!'),
])->assignRole('super-admin');
```

---

## Phase 3: Services & Business Logic

### 3.1 PricingService (`App\Services\PricingService`) (UPDATED)
```php
Methods:
- calculatePrice(PricingTier $tier): int
  - Return price in Rupiah based on tier
  
- getAvailableTiers(Country $country, RegistrationType $type): array
  - Return appropriate pricing tiers based on:
    * Country (local vs international)
    * Registration type (online vs offline)
  
  Examples:
  - Local + Online: [ONLINE_LOCAL_SNACK_ONLY, ONLINE_LOCAL_SNACK_LUNCH]
  - Local + Offline: [OFFLINE_LOCAL_SNACK_LUNCH_1, OFFLINE_LOCAL_SNACK_LUNCH_2]
  - International + Online: [ONLINE_INTERNATIONAL_SNACK_LUNCH]
  - International + Offline: [OFFLINE_INTERNATIONAL_SNACK_LUNCH]
  
- formatPrice(int $amount): string
  - Return "IDR X.XXX.XXX" format
```

### 3.2 LocationService (`App\Services\LocationService`) (NEW)
```php
Methods:
- isUserOnSite(float $userLat, float $userLng): bool
  - Get venue coordinates from settings
  - Calculate distance between user and venue using Haversine formula
  - Return true if within venue_detection_radius
  
- calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
  - Calculate distance in meters between two coordinates
  - Use Haversine formula for accuracy
  
- getVenueCoordinates(): array
  - Return venue lat/lng from settings
  
- getDetectionRadius(): int
  - Return radius in meters from settings
```

### 3.3 RegistrationService (`App\Services\RegistrationService`)
```php
Methods:
- generateUniqueCode(): string
  - Generate format: SEM-2026-XXXX
  - Check for uniqueness
  
- sendSubmissionConfirmation(SeminarRegistration $registration): void
  - Send email with registration code and payment instructions
  - Include registration type (online/offline) in email
  
- sendVerificationNotification(SeminarRegistration $registration): void
  - Send email when payment is verified
  
- sendRejectionNotification(SeminarRegistration $registration): void
  - Send email with rejection reason
```

---

## Phase 4: Filament Admin Panel

### 4.1 Panel Configuration
- Default Filament panel at `/admin`
- Authentication required
- Navigation groups: "Registrations", "Settings", "System"

### 4.2 Resources

#### `VisitorResource`
```
Model: App\Models\Visitor

Pages:
- ListVisitors - Table view
- ViewVisitor - Detail view (NO create/edit/delete)

Table Columns:
- Name (sortable, searchable)
- Email (sortable, searchable)
- Phone
- Profession
- Preferred Visit Date (sortable)
- Marketing Source
- Created At (sortable)

Filters:
- Preferred Visit Date
- Profession
- Marketing Source

Actions:
- Export to CSV (bulk action)
- Export to Excel (bulk action)

Widget:
- Total visitors count
- Visitors by date chart
```

#### `SeminarRegistrationResource` (UPDATED)
```
Model: App\Models\SeminarRegistration

Pages:
- ListSeminarRegistrations - Table view
- CreateSeminarRegistration - Form (admin can create on behalf of user)
- EditSeminarRegistration - Form
- ViewSeminarRegistration - Detail view

Table Columns:
- Registration Code (searchable)
- Name (sortable, searchable)
- Email (searchable)
- Country
- Registration Type (badge - "Online" or "On-site") (NEW)
- Pricing Tier
- Amount (sortable)
- Payment Status (badge with color)
- Created At (sortable)

Filters:
- Payment Status (pending, verified, rejected)
- Registration Type (online, offline) (NEW)
- Country
- Pricing Tier
- Date Range

Form Fields:
- Section: "Personal Information"
  - Name (text, required)
  - Email (email, required, unique)
  - Phone (text, required)
  - Affiliation (text)
  - Country (select, required)
  
- Section: "Registration Details" (UPDATED)
  - Registration Type (select: Online/Offline) - Required
  - Pricing Tier (select, required)
    - Options filtered by BOTH country AND registration_type
    - Example flows:
      * Indonesia + Online: [600k Snack Only, 900k Snack+Lunch]
      * Indonesia + Offline: [900k Snack+Lunch, 1200k Snack+Lunch]
      * International + Online: [1500k Snack+Lunch]
      * International + Offline: [2500k Snack+Lunch]
  - Amount (numeric, disabled, auto-calculated)
  
- Section: "Payment Information"
  - Payment Status (select)
  - Payment Proof (file upload, accept: jpg,png,pdf)
  - Rejection Reason (textarea, visible only if status=rejected)
  
- Section: "Verification"
  - Verified By (display only)
  - Verified At (display only)

Actions:
- Verify Payment (action button)
  - Only visible for pending registrations
  - Sets status to verified
  - Records verified_by and verified_at
  - Sends verification email
  
- Reject Payment (action with form)
  - Only visible for pending registrations
  - Modal with rejection reason textarea
  - Sets status to rejected
  - Records rejection_reason
  - Sends rejection email
  
- View Payment Proof (action)
  - Opens modal with image/PDF viewer
  - Only visible if payment_proof_path exists

Widget:
- Total registrations count
- Online vs Offline breakdown (pie chart) (NEW)
- Pending payments count
- Verified payments count
- Total revenue (sum of verified amounts)
- Payment status breakdown (pie chart)
```

#### `CountryResource`
```
Model: App\Models\Country

Pages: Standard CRUD (List, Create, Edit)

Table Columns:
- Name (sortable, searchable)
- Code (sortable)
- Is Local (badge)
- Phone Code
- Registrations Count

Form Fields:
- Name (text, required)
- Code (text, required, 3 chars)
- Is Local (toggle)
- Phone Code (text)
```

#### `SettingResource` (UPDATED)
```
Model: App\Models\Setting

Pages: List, Edit (NO create/delete)

Settings to manage:
- Bank Account Name
- Bank Account Number
- Bank Name
- Payment Instructions (rich text)
- Event Terms & Conditions (rich text, optional)

NEW - Venue Settings:
- Venue Name (text) - e.g., "Jakarta Convention Center"
- Venue Address (textarea)
- Venue Latitude (decimal, 10, 8) - For geolocation
- Venue Longitude (decimal, 11, 8) - For geolocation
- Detection Radius (number) - In meters (default: 500)

Form Fields:
- Section: "Payment Settings"
  - Bank Account Name (text)
  - Bank Account Number (text)
  - Bank Name (text)
  - Payment Instructions (rich text editor)
  
- Section: "Event Settings"
  - Event Terms & Conditions (rich text editor)
  
- Section: "Venue Location Settings" (NEW)
  - Venue Name (text)
  - Venue Address (textarea)
  - Venue Latitude (number, step=0.00000001)
  - Venue Longitude (number, step=0.00000001)
  - Detection Radius in meters (number, default: 500)
  - Helper text: "Radius in meters for on-site detection. Recommended: 100-1000 meters."
  - Map Preview (optional - show venue on map)
```

#### `ProfessionResource`
```
Model: App\Models\Profession

Pages: Standard CRUD

Table Columns:
- Name (sortable)
- Sort Order (sortable)

Form Fields:
- Name (text, required)
- Sort Order (number)
```

#### `MarketingSourceResource`
```
Model: App\Models\MarketingSource

Pages: Standard CRUD

Table Columns:
- Name (sortable)
- Sort Order (sortable)

Form Fields:
- Name (text, required)
- Sort Order (number)
```

---

## Phase 5: Public Registration Forms (Livewire)

### 5.1 Visitor Registration Component

#### `app/Livewire/VisitorRegistration.php`
```php
Properties:
- $name (string)
- $email (string)
- $phone (string)
- $affiliation (string)
- $profession (string)
- $preferred_visit_date (string)
- $marketing_source (string)
- $professions (collection)
- $marketing_sources (collection)

Rules:
- name: required|string|max:255
- email: required|email|unique:visitors,email
- phone: required|string|max:20
- affiliation: nullable|string|max:255
- profession: required|string
- preferred_visit_date: required|date|in:2026-11-13,2026-11-14,2026-11-15
- marketing_source: nullable|string

Methods:
- mount() - Load professions and marketing sources
- submit() - Validate and create visitor, send email, show success

Events:
- success - Emit when registration is successful
```

#### `resources/views/livewire/visitor-registration.blade.php`
```
Layout:
- Logo at top
- Title: "Visitor Registration"
- Form with Tailwind styling

Form Fields:
- Name (text input)
- Email (email input)
- Phone (text input with phone icon)
- Affiliation/Institution (text input)
- Profession (select dropdown)
- Preferred Visit Date (date picker, only 13-15 Nov 2026)
- Marketing Source (select dropdown)

Buttons:
- "Register" (primary button, loading state)

States:
- Loading (spinner, disabled button)
- Success (success message)
- Error (error messages)
```

### 5.2 Seminar Registration Component (UPDATED with Geolocation)

#### `app/Livewire/SeminarRegistration.php`
```php
Properties:
- $name (string)
- $email (string)
- $phone (string)
- $affiliation (string)
- $country_id (int)
- $registration_type (string) - 'online' or 'offline' (NEW)
- $pricing_tier (string)
- $amount (int)
- $payment_proof (UploadedFile)
- $countries (collection)
- $available_tiers (array)
- $payment_instructions (string)
- $user_latitude (float|null) - From geolocation (NEW)
- $user_longitude (float|null) - From geolocation (NEW)
- $location_detected (bool) - Track if location was detected (NEW)
- $manual_override (bool) - Allow manual type selection (NEW)

Rules:
- name: required|string|max:255
- email: required|email|unique:seminar_registrations,email
- phone: required|string|max:20
- affiliation: nullable|string|max:255
- country_id: required|exists:countries,id
- registration_type: required|in:online,offline
- pricing_tier: required
- payment_proof: required|file|mimes:jpg,jpeg,png,pdf|max:5120

Computed Properties:
- availableTiers() - Returns available tiers based on country AND registration_type
- formattedAmount() - Returns formatted price

Listeners:
- updatedCountryId() - Update available tiers when country changes
- updatedRegistrationType() - Update available tiers when type changes (NEW)
- updatedPricingTier() - Update amount when tier changes
- locationDetected($lat, $lng) - Receive geolocation data from frontend (NEW)

Methods:
- mount() - Load countries, payment instructions, initialize
- detectLocation() - Placeholder for location detection trigger
- setLocation($lat, $lng) - Process detected location and determine registration type
- submit() - Validate, upload payment proof, create registration, send email

Events:
- success - Emit when registration is successful
```

#### `resources/views/livewire/seminar-registration.blade.php` (UPDATED)
```
Layout:
- Logo at top
- Title: "Seminar Registration"
- Form with Tailwind styling

Section 0: Location Detection Banner (NEW)
- Displayed at top of form
- Shows detection status:
  * "Detecting your location..." (spinner)
  * "On-site registration detected" (green badge)
  * "Online registration" (blue badge)
  * "Location access denied - Online registration" (warning)
- Manual Override Toggle (optional):
  * "I'm registering on-site (at the venue)"
  * Checkbox to manually switch between online/offline

Section 1: Personal Information
- Name (text input)
- Email (email input)
- Phone (text input)
- Affiliation (text input)
- Country (select dropdown)

Section 2: Registration Package (UPDATED)
- Registration Type Display (read-only, auto-detected):
  * "Online Registration" or "On-site Registration"
  * Badge with icon
- Pricing Tier (select dropdown, options change based on BOTH country AND registration_type):
  - Local + Online:
    * "Snack Only - IDR 600.000"
    * "Snack + Lunch - IDR 900.000"
  - Local + Offline:
    * "Snack + Lunch - IDR 900.000 (Tier 1)"
    * "Snack + Lunch - IDR 1.200.000 (Tier 2)"
  - International + Online:
    * "Snack + Lunch - IDR 1.500.000"
  - International + Offline:
    * "Snack + Lunch - IDR 2.500.000"
- Amount Display (read-only, large text)
  * "Total: IDR 600.000"

Section 3: Payment Information
- Payment Instructions Box (styled box with bank details)
  * "Bank Name: [From Settings]"
  * "Account Name: [From Settings]"
  * "Account Number: [From Settings]"
  * "Please transfer the exact amount and upload your payment proof"
- Payment Proof Upload (file input with drag & drop)

Buttons:
- "Submit Registration" (primary button, loading state)

States:
- Loading (spinner, disabled button)
- Detecting Location (geolocation in progress)
- Success (success message with registration code)
- Error (error messages)

JavaScript Integration:
- On component mount:
  1. Request browser geolocation permission
  2. Get current position
  3. Send coordinates to Livewire via $wire.setLocation(lat, lng)
  4. Handle errors gracefully (permission denied, unavailable, timeout)
- Watch for location changes and update registration_type
```

---

## Phase 6: Email System

### 6.1 Mailables

#### `App\Mail\VisitorRegistrationConfirmation`
```php
Subject: "Welcome to Jakarta Dental Exhibition 2026!"

Content:
- Logo
- Greeting with visitor name
- Registration details:
  * Preferred visit date
  * Event venue and time
- What to bring
- Contact information
- Footer with event details
```

#### `App\Mail\SeminarRegistrationSubmitted` (UPDATED)
```php
Subject: "Seminar Registration Received - [Registration Code]"

Content:
- Logo
- Greeting with registrant name
- Registration details:
  * Registration Code (prominent display)
  * Registration Type (Online/On-site) (NEW)
  * Pricing tier and amount
  * Meal preference
- Payment instructions:
  * Bank account details
  * Payment deadline
  * What happens next
- Important notes
- Contact information
- Footer
```

#### `App\Mail\SeminarPaymentVerified`
```php
Subject: "Payment Verified - See You at the Event! [Registration Code]"

Content:
- Logo
- Greeting
- Verification confirmation
- Registration details:
  * Registration Code
  * Event dates: 13-15 November 2026
  * Venue details
- What to bring:
  * This confirmation email
  * ID card
  * Registration code
- Event schedule overview
- Contact information
- Footer
```

#### `App\Mail\SeminarPaymentRejected`
```php
Subject: "Payment Verification Issue - Action Required [Registration Code]"

Content:
- Logo
- Greeting
- Issue notification
- Reason for rejection
- Instructions to resolve:
  * How to re-upload payment proof
  * Contact if questions
- Registration code for reference
- Contact information
- Footer
```

### 6.2 Email Configuration
- Use Markdown emails for responsive templates
- Include logo in all emails
- Set reply-to address if needed
- Queue emails for better performance

---

## Phase 7: File Storage

### 7.1 Configuration
```php
// config/filesystems.php
'disks' => [
    'payment-proofs' => [
        'driver' => 'local',
        'root' => storage_path('app/payment-proofs'),
        'visibility' => 'private',
    ],
],
```

### 7.2 Secure File Access
- Create route: `GET /admin/payment-proof/{registration}`
- Controller method to check admin auth and serve file
- Return file as response

---

## Phase 8: Routes

### 8.1 Public Routes
```php
// routes/web.php

GET / - Welcome page (optional, can redirect to registration)
GET /register/visitor - Visitor registration form
GET /register/seminar - Seminar registration form (auto-detects online/offline)
```

### 8.2 Admin Routes
```php
// Auto-generated by Filament at /admin/*
```

---

## Phase 9: Seeders

### 9.1 CountrySeeder
Populate with all countries, including:
- Indonesia (is_local = true, code = IDN, phone_code = +62)
- Singapore (code = SGP)
- Malaysia (code = MYS)
- Thailand (code = THA)
- ... (all other countries)

### 9.2 ProfessionSeeder
```php
Dentist
Dental Student
Dental Hygienist
Dental Assistant
Dental Technician
Oral Surgeon
Orthodontist
Periodontist
Other
```

### 9.3 MarketingSourceSeeder
```php
Social Media (Instagram, Facebook, etc.)
Colleague/Friend
Email Campaign
Website
Dental Association
Other
```

### 9.4 SettingSeeder (UPDATED)
```php
// Payment Settings
bank_account_name: "PT Jakarta Dental Exhibition" (placeholder)
bank_account_number: "1234567890" (placeholder)
bank_name: "Bank Central Asia (BCA)" (placeholder)
payment_instructions: "Please transfer the registration fee to the bank account above..."

// Event Settings
event_terms_conditions: "" (empty initially)

// Venue Settings (NEW)
venue_name: "Jakarta Convention Center" (placeholder)
venue_address: "Jl. Gatot Subroto, Jakarta Pusat, Indonesia" (placeholder)
venue_latitude: -6.2088 (example - update with actual venue)
venue_longitude: 106.8456 (example - update with actual venue)
venue_detection_radius: 500 (meters)
```

### 9.5 SuperAdminSeeder
Create super admin user with provided credentials

### 9.6 RolePermissionSeeder
Create roles, permissions, and assign permissions to admin role

---

## Phase 10: Testing

### 10.1 Unit Tests
- `PricingServiceTest` (UPDATED)
  - Test price calculation for each tier (6 tiers)
  - Test available tiers for local vs international
  - Test available tiers for online vs offline
  - Test price formatting
  
- `LocationServiceTest` (NEW)
  - Test distance calculation
  - Test on-site detection logic
  - Test boundary conditions
  
- `RegistrationCodeTest`
  - Test unique code generation
  - Test format validation

### 10.2 Feature Tests
- `VisitorRegistrationTest`
  - Test successful registration
  - Test validation errors
  - Test duplicate email
  - Test email sent
  
- `SeminarRegistrationTest` (UPDATED)
  - Test successful registration (online)
  - Test successful registration (offline)
  - Test validation errors
  - Test payment proof upload
  - Test pricing tier filtering by country AND registration_type
  - Test amount calculation
  - Test email sent
  
- `PaymentVerificationTest`
  - Test admin can verify payment
  - Test admin can reject payment
  - Test verification email sent
  - Test rejection email sent
  - Test unauthorized access

### 10.3 Livewire Tests
- `VisitorRegistrationComponentTest`
  - Test form validation
  - Test successful submission
  - Test error states
  
- `SeminarRegistrationComponentTest` (UPDATED)
  - Test form validation
  - Test country change updates pricing tiers
  - Test registration_type change updates pricing tiers
  - Test tier change updates amount
  - Test file upload validation
  - Test successful submission
  - Test location detection flow

---

## Phase 11: UI/UX Styling

### 11.1 Tailwind Configuration
- Mobile-first responsive design
- Custom colors if needed
- Form components styling

### 11.2 Form Styling
- Clean, modern design
- Clear labels and placeholders
- Location detection banner
- Registration type badge
- Validation feedback (green checkmarks, red errors)
- Loading spinners
- Success animations
- File upload with drag & drop UI

### 11.3 Email Styling
- Responsive email templates
- Logo placement
- Clear typography
- Professional color scheme

---

## Phase 12: JavaScript Integration (NEW)

### 12.1 Geolocation Script
```javascript
// Inline script in seminar-registration.blade.php

document.addEventListener('DOMContentLoaded', function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            // Success callback
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                // Send to Livewire component
                @this.setLocation(lat, lng);
            },
            // Error callback
            function(error) {
                console.warn('Geolocation error:', error.message);
                // Default to online registration
                @this.setLocation(null, null);
            },
            // Options
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    } else {
        // Geolocation not supported, default to online
        @this.setLocation(null, null);
    }
});
```

### 12.2 Livewire Integration
```php
// In SeminarRegistration.php

#[On('setLocation')]
public function setLocation($lat, $lng)
{
    if ($lat && $lng) {
        $locationService = app(LocationService::class);
        $this->user_latitude = $lat;
        $this->user_longitude = $lng;
        
        // Determine if user is on-site
        if ($locationService->isUserOnSite($lat, $lng)) {
            $this->registration_type = RegistrationType::OFFLINE->value;
            $this->location_detected = true;
        } else {
            $this->registration_type = RegistrationType::ONLINE->value;
            $this->location_detected = true;
        }
    } else {
        // Default to online if location unavailable
        $this->registration_type = RegistrationType::ONLINE->value;
        $this->location_detected = false;
    }
    
    // Update available pricing tiers
    $this->updateAvailableTiers();
}
```

---

## Implementation Order

### Step 1: Foundation
1. Install spatie/laravel-permission
2. Publish permission config & migrations
3. Create all migrations (countries, visitors, seminar_registrations with registration_type, settings with venue fields, professions, marketing_sources)
4. Run migrations

### Step 2: Models & Enums
1. Create all models with relationships
2. Create enums (RegistrationType, PricingTier with 6 tiers, PaymentStatus)
3. Add accessors and methods

### Step 3: Authorization
1. Update User model with HasRoles trait
2. Configure Gate::before in AppServiceProvider
3. Create RolePermissionSeeder
4. Create SuperAdminSeeder
5. Run seeders

### Step 4: Services
1. Create PricingService (updated for 6 tiers)
2. Create LocationService (new)
3. Create RegistrationService

### Step 5: Email System
1. Create all mailables
2. Create email templates
3. Configure mail settings

### Step 6: Filament Panel
1. Configure Filament panel
2. Create all Filament resources (including updated SettingResource with venue fields)
3. Configure permissions for each resource
4. Create dashboard widgets

### Step 7: Public Forms
1. Create VisitorRegistration Livewire component
2. Create SeminarRegistration Livewire component (with geolocation)
3. Create views with Tailwind styling
4. Integrate JavaScript geolocation
5. Add routes

### Step 8: File Storage
1. Configure payment-proofs disk
2. Create secure file access route/controller

### Step 9: Seeders
1. Create CountrySeeder
2. Create ProfessionSeeder
3. Create MarketingSourceSeeder
4. Create SettingSeeder (with venue coordinates)
5. Run all seeders

### Step 10: Testing
1. Write unit tests
2. Write feature tests
3. Write Livewire component tests
4. Write geolocation tests
5. Run all tests

### Step 11: Final Polish
1. Style all frontend components
2. Test location detection flow
3. Test all user flows (online and offline)
4. Verify email sending
5. Test payment verification workflow
6. Run Pint for code formatting

---

## Technical Specifications

### Currency Formatting
```php
// Amount stored as integer (no decimals)
$amount = 600000;

// Display format: "IDR 600.000"
$formatted = 'IDR ' . number_format($amount, 0, ',', '.');
```

### Event Dates Validation
```php
// Valid dates: 2026-11-13, 2026-11-14, 2026-11-15
$validDates = [
    '2026-11-13',
    '2026-11-14', 
    '2026-11-15',
];

// In form validation
'preferred_visit_date' => ['required', 'date', 'in:' . implode(',', $validDates)]
```

### Geolocation Configuration
```php
// Venue coordinates stored in settings
$venueLat = Setting::get('venue_latitude');   // e.g., -6.2088
$venueLng = Setting::get('venue_longitude');  // e.g., 106.8456
$radius = Setting::get('venue_detection_radius', 500); // meters

// Haversine formula for distance calculation
// Returns distance in meters
function calculateDistance($lat1, $lng1, $lat2, $lng2) {
    $earthRadius = 6371000; // meters
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLng/2) * sin($dLng/2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}
```

### Payment Proof Security
- Accepted formats: jpg, jpeg, png, pdf
- Max size: 5MB (5120 KB)
- Stored in private storage
- Accessible only via authenticated admin route

### Email Aliasing
Configure in `.env`:
```
MAIL_FROM_ADDRESS="noreply@jakartadentalexhibitions.id"
MAIL_FROM_NAME="Jakarta Dental Exhibition"
```

### Registration Code Format
```
SEM-2026-0001
SEM-2026-0002
SEM-2026-0003
...

Format: {PREFIX}-{YEAR}-{SEQUENTIAL_NUMBER}
```

---

## Pricing Structure Reference

### ONLINE REGISTRATION (Pre-event)
| Tier | Target | Price | Meal |
|------|--------|-------|------|
| ONLINE_LOCAL_SNACK_ONLY | Local (Indonesia) | IDR 600.000 | Snack only |
| ONLINE_LOCAL_SNACK_LUNCH | Local (Indonesia) | IDR 900.000 | Snack + Lunch |
| ONLINE_INTERNATIONAL_SNACK_LUNCH | International | IDR 1.500.000 | Snack + Lunch |

### OFFLINE REGISTRATION (On-site)
| Tier | Target | Price | Meal |
|------|--------|-------|------|
| OFFLINE_LOCAL_SNACK_LUNCH_1 | Local (Indonesia) | IDR 900.000 | Snack + Lunch |
| OFFLINE_LOCAL_SNACK_LUNCH_2 | Local (Indonesia) | IDR 1.200.000 | Snack + Lunch |
| OFFLINE_INTERNATIONAL_SNACK_LUNCH | International | IDR 2.500.000 | Snack + Lunch |

---

## User Registration Flow

### Online Registration Flow
1. User visits `/register/seminar` from home/office
2. Browser requests location permission
3. User denies or location is outside venue radius
4. System sets `registration_type = 'online'`
5. User sees ONLINE pricing tiers
6. User fills form, uploads payment proof
7. System sends confirmation email
8. Admin verifies payment
9. User receives verification email

### On-site Registration Flow
1. Attendee scans QR code at venue kiosk
2. Opens `/register/seminar` on phone/kiosk
3. Browser requests location permission
4. Location detected within venue radius
5. System sets `registration_type = 'offline'`
6. Attendee sees OFFLINE pricing tiers
7. Attendee fills form, uploads payment proof
8. System sends confirmation email
9. Admin verifies payment
10. Attendee receives verification email

---

## Success Criteria

✅ **Visitor Registration**
- Visitors can register with all required fields
- Email confirmation sent automatically
- Admin can view and export visitor data

✅ **Seminar Registration (Online)**
- Remote users see ONLINE pricing tiers
- Pricing tiers filtered by country
- Payment proof uploaded successfully
- Registration code generated and displayed
- Email confirmation sent with payment instructions
- Admin can verify/reject payments
- Status update emails sent automatically

✅ **Seminar Registration (Offline)**
- On-site users detected via geolocation
- On-site users see OFFLINE pricing tiers
- Pricing tiers filtered by country
- Payment proof uploaded successfully
- Registration code generated and displayed
- Email confirmation sent with payment instructions
- Admin can verify/reject payments
- Status update emails sent automatically

✅ **Geolocation Detection**
- Browser geolocation works correctly
- Distance calculation accurate
- On-site detection works within venue radius
- Graceful fallback when location denied/unavailable
- Manual override available if needed

✅ **Admin Panel**
- Super admin has full access
- Admin has limited access based on permissions
- Payment verification workflow works correctly
- Venue coordinates configurable in settings
- All data can be exported
- Registration type visible in listings

✅ **Email System**
- All emails sent successfully
- Emails formatted correctly with logo
- Registration type included in emails
- Responsive email templates

✅ **Security**
- Payment proofs stored securely
- Admin-only access to sensitive data
- Proper authorization checks

✅ **Testing**
- All tests pass
- Geolocation tests pass
- Code coverage adequate
- No critical bugs

---

## Next Steps

1. ✅ Review this updated implementation plan
2. ✅ Confirm venue coordinates and detection radius
3. ✅ Begin implementation in the order specified
4. ✅ Test each phase before moving to the next
5. ✅ Test geolocation thoroughly with real devices
6. ✅ Run all tests and verification before deployment

---

**Ready to implement? Confirm venue details and we can begin!**