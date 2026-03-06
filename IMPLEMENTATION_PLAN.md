# Dental Exhibition Registration System - Implementation Plan

## Project Overview
A comprehensive registration system for Jakarta Dental Exhibition 2026 featuring:
- **Visitor Registration** (Free entrance tracking)
- **Seminar Registration** (Paid, with payment verification)
- **Admin Panel** (Filament-powered management system)

**Event Dates:** 13-15 November 2026  
**Event Logo:** `/public/assets/images/Jade_Logo.webp`

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

#### `create_seminar_registrations_table`
- `id` (bigint, primary key)
- `registration_code` (string, unique) - Format: SEM-2026-0001
- `name` (string)
- `email` (string, unique)
- `phone` (string)
- `affiliation` (string, nullable)
- `country_id` (foreign key -> countries.id)
- `pricing_tier` (enum: 'local_snack_only', 'local_snack_lunch', 'international_snack_lunch')
- `amount` (unsignedBigInteger) - Amount in Rupiah (600000, 900000, 1500000)
- `payment_status` (enum: 'pending', 'verified', 'rejected')
- `payment_proof_path` (string, nullable)
- `rejection_reason` (text, nullable)
- `verified_by` (foreign key nullable -> users.id)
- `verified_at` (timestamp, nullable)
- `created_at`, `updated_at`

#### `create_settings_table`
- `id` (bigint, primary key)
- `key` (string, unique) - Setting identifier
- `value` (text) - Setting value (JSON or plain text)
- `created_at`, `updated_at`

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

#### `App\Models\SeminarRegistration`
```php
Relationships:
- belongsTo(Country::class)
- belongsTo(User::class, 'verified_by')

Fillable: registration_code, name, email, phone, affiliation, country_id, pricing_tier, amount, payment_status, payment_proof_path, rejection_reason, verified_by, verified_at

Casts:
- amount => 'integer'
- verified_at => 'datetime'

Accessors:
- getFormattedAmountAttribute() - "IDR 600.000"
- getStatusBadgeAttribute() - Return color for badge

Methods:
- generateRegistrationCode() - Generate unique SEM-2026-XXXX
- static findByCode(string $code) - Find by registration code
```

#### `App\Models\Setting`
```php
Fillable: key, value

Casts:
- value => 'json' (if needed)

Static Methods:
- static get(string $key, mixed $default = null) - Retrieve setting value
- static set(string $key, mixed $value) - Set setting value
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

#### `App\Enums\PricingTier`
```php
LOCAL_SNACK_ONLY = 'local_snack_only'       // IDR 600.000
LOCAL_SNACK_LUNCH = 'local_snack_lunch'     // IDR 900.000
INTERNATIONAL_SNACK_LUNCH = 'international_snack_lunch' // IDR 1.500.000

Methods:
- getLabel(): string
  - LOCAL_SNACK_ONLY => "Local - Snack Only"
  - LOCAL_SNACK_LUNCH => "Local - Snack + Lunch"
  - INTERNATIONAL_SNACK_LUNCH => "International - Snack + Lunch"
  
- getPrice(): int
  - Returns 600000, 900000, or 1500000
  
- getFormattedPrice(): string
  - Returns "IDR 600.000", "IDR 900.000", "IDR 1.500.000"
  
- getMealPreference(): string
  - Returns "Snack only" or "Snack + Lunch"
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
- `manage settings` - Edit system settings
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

### 3.1 PricingService (`App\Services\PricingService`)
```php
Methods:
- calculatePrice(Country $country, PricingTier $tier): int
  - Validate tier is available for country
  - Return price in Rupiah
  
- getAvailableTiers(Country $country): array
  - If country is_local = true: return [LOCAL_SNACK_ONLY, LOCAL_SNACK_LUNCH]
  - If country is_local = false: return [INTERNATIONAL_SNACK_LUNCH]
  
- formatPrice(int $amount): string
  - Return "IDR X.XXX.XXX" format
```

### 3.2 RegistrationService (`App\Services\RegistrationService`)
```php
Methods:
- generateUniqueCode(): string
  - Generate format: SEM-2026-XXXX
  - Check for uniqueness
  
- sendSubmissionConfirmation(SeminarRegistration $registration): void
  - Send email with registration code and payment instructions
  
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

#### `SeminarRegistrationResource`
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
- Pricing Tier
- Amount (sortable)
- Payment Status (badge with color)
- Created At (sortable)

Filters:
- Payment Status (pending, verified, rejected)
- Country
- Pricing Tier
- Date Range

Form Fields:
- Section: "Personal Information"
  - Name (text, required)
  - Email (email, required, unique)
  - Phone (text, required)
  - Affiliation (text)
  - Country (select, required) - Reactive field
  
- Section: "Registration Details"
  - Pricing Tier (select, required)
    - Options depend on selected country
    - Local: [Snack Only (600k), Snack+Lunch (900k)]
    - International: [Snack+Lunch (1500k)]
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

#### `SettingResource`
```
Model: App\Models\Setting

Pages: List, Edit (NO create/delete)

Settings to manage:
- bank_account_name
- bank_account_number
- bank_name
- payment_instructions (rich text)
- event_terms_conditions (rich text, optional)

Form Fields:
- Bank Account Name (text)
- Bank Account Number (text)
- Bank Name (text)
- Payment Instructions (rich text editor)
- Event Terms & Conditions (rich text editor)
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
- $professions (collection) - Loaded from database
- $marketing_sources (collection) - Loaded from database

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
- Success (success message, redirect after 3 seconds)
- Error (error messages)
```

### 5.2 Seminar Registration Component

#### `app/Livewire/SeminarRegistration.php`
```php
Properties:
- $name (string)
- $email (string)
- $phone (string)
- $affiliation (string)
- $country_id (int)
- $pricing_tier (string)
- $amount (int)
- $payment_proof (UploadedFile)
- $countries (collection)
- $available_tiers (array)
- $payment_instructions (string) - From settings

Rules:
- name: required|string|max:255
- email: required|email|unique:seminar_registrations,email
- phone: required|string|max:20
- affiliation: nullable|string|max:255
- country_id: required|exists:countries,id
- pricing_tier: required|in:local_snack_only,local_snack_lunch,international_snack_lunch
- payment_proof: required|file|mimes:jpg,jpeg,png,pdf|max:5120

Computed Properties:
- availableTiers() - Returns available tiers based on selected country
- formattedAmount() - Returns formatted price

Listeners:
- updatedCountryId() - Update available tiers when country changes
- updatedPricingTier() - Update amount when tier changes

Methods:
- mount() - Load countries and payment instructions
- submit() - Validate, upload payment proof, create registration, send email

Events:
- success - Emit when registration is successful
```

#### `resources/views/livewire/seminar-registration.blade.php`
```
Layout:
- Logo at top
- Title: "Seminar Registration"
- Form with Tailwind styling

Section 1: Personal Information
- Name (text input)
- Email (email input)
- Phone (text input)
- Affiliation (text input)
- Country (select dropdown) - Reactive

Section 2: Registration Package
- Pricing Tier (select dropdown, options change based on country)
  - Local: 
    * "Snack Only - IDR 600.000"
    * "Snack + Lunch - IDR 900.000"
  - International:
    * "Snack + Lunch - IDR 1.500.000"
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
- Success (success message with registration code, redirect after 5 seconds)
- Error (error messages)
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

#### `App\Mail\SeminarRegistrationSubmitted`
```php
Subject: "Seminar Registration Received - [Registration Code]"

Content:
- Logo
- Greeting with registrant name
- Registration details:
  * Registration Code (prominent display)
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
GET /register/seminar - Seminar registration form
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

### 9.4 SettingSeeder
```php
bank_account_name: "PT Jakarta Dental Exhibition" (placeholder)
bank_account_number: "1234567890" (placeholder)
bank_name: "Bank Central Asia (BCA)" (placeholder)
payment_instructions: "Please transfer the registration fee to the bank account above..."
event_terms_conditions: "" (empty initially)
```

### 9.5 SuperAdminSeeder
Create super admin user with provided credentials

### 9.6 RolePermissionSeeder
Create roles, permissions, and assign permissions to admin role

---

## Phase 10: Testing

### 10.1 Unit Tests
- `PricingServiceTest`
  - Test price calculation for each tier
  - Test available tiers for local vs international
  - Test price formatting
  
- `RegistrationCodeTest`
  - Test unique code generation
  - Test format validation

### 10.2 Feature Tests
- `VisitorRegistrationTest`
  - Test successful registration
  - Test validation errors
  - Test duplicate email
  - Test email sent
  
- `SeminarRegistrationTest`
  - Test successful registration
  - Test validation errors
  - Test payment proof upload
  - Test pricing tier filtering by country
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
  
- `SeminarRegistrationComponentTest`
  - Test form validation
  - Test country change updates pricing tiers
  - Test tier change updates amount
  - Test file upload validation
  - Test successful submission

---

## Phase 11: UI/UX Styling

### 11.1 Tailwind Configuration
- Mobile-first responsive design
- Custom colors if needed
- Form components styling

### 11.2 Form Styling
- Clean, modern design
- Clear labels and placeholders
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

## Implementation Order

### Step 1: Foundation
1. Install spatie/laravel-permission
2. Publish permission config & migrations
3. Create all migrations (countries, visitors, seminar_registrations, settings, professions, marketing_sources)
4. Run migrations

### Step 2: Models & Enums
1. Create all models with relationships
2. Create enums (PricingTier, PaymentStatus)
3. Add accessors and methods

### Step 3: Authorization
1. Update User model with HasRoles trait
2. Configure Gate::before in AppServiceProvider
3. Create RolePermissionSeeder
4. Create SuperAdminSeeder
5. Run seeders

### Step 4: Services
1. Create PricingService
2. Create RegistrationService

### Step 5: Email System
1. Create all mailables
2. Create email templates
3. Configure mail settings

### Step 6: Filament Panel
1. Configure Filament panel
2. Create all Filament resources
3. Configure permissions for each resource
4. Create dashboard widgets

### Step 7: Public Forms
1. Create VisitorRegistration Livewire component
2. Create SeminarRegistration Livewire component
3. Create views with Tailwind styling
4. Add routes

### Step 8: File Storage
1. Configure payment-proofs disk
2. Create secure file access route/controller

### Step 9: Seeders
1. Create CountrySeeder
2. Create ProfessionSeeder
3. Create MarketingSourceSeeder
4. Create SettingSeeder
5. Run all seeders

### Step 10: Testing
1. Write unit tests
2. Write feature tests
3. Write Livewire component tests
4. Run all tests

### Step 11: Final Polish
1. Style all frontend components
2. Test all user flows
3. Verify email sending
4. Test payment verification workflow
5. Run Pint for code formatting

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

## Success Criteria

✅ **Visitor Registration**
- Visitors can register with all required fields
- Email confirmation sent automatically
- Admin can view and export visitor data

✅ **Seminar Registration**
- Registrants can select country and see appropriate pricing tiers
- Payment proof uploaded successfully
- Registration code generated and displayed
- Email confirmation sent with payment instructions
- Admin can verify/reject payments
- Status update emails sent automatically

✅ **Admin Panel**
- Super admin has full access
- Admin has limited access based on permissions
- Payment verification workflow works correctly
- All data can be exported

✅ **Email System**
- All emails sent successfully
- Emails formatted correctly with logo
- Responsive email templates

✅ **Security**
- Payment proofs stored securely
- Admin-only access to sensitive data
- Proper authorization checks

✅ **Testing**
- All tests pass
- Code coverage adequate
- No critical bugs

---

## Next Steps

1. Review this implementation plan
2. Ask any clarifying questions
3. Once approved, begin implementation in the order specified
4. Test each phase before moving to the next
5. Run all tests and verification before deployment

---

**Ready to implement? Let me know if you have any questions or modifications!**