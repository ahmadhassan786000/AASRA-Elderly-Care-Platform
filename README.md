# AASRA — PHP / MySQL

**Connecting Care, Building Trust.**

AASRA is a PHP and MySQL web application that connects elderly and differently-abled people with care and assistance providers. It includes a public website, user dashboard, provider dashboard, and admin panel.

## 1. Requirements

- XAMPP
- Apache
- MySQL / MariaDB
- PHP 8.0 or later
- PHP extensions: `mysqli`, `mbstring`, and `fileinfo`
- MySQLi procedural database connectivity

## 2. Installation (XAMPP)

1. Copy the project folder to:

   `C:\xampp\htdocs\aasra`

2. Open XAMPP Control Panel and start **Apache** and **MySQL**.

3. Open phpMyAdmin:

   `http://localhost/phpmyadmin/`

4. Create a database named:

   `aasra_db`

5. Select `aasra_db`, open the **Import** tab, and import:

   `database/aasra_db.sql`

   **Important:** Importing this SQL file recreates database tables and can delete existing data. Back up your database before importing it. Do not import it over an existing database unless you intend to reset that database.

6. Open the application:

   `http://localhost/aasra/`

   The application automatically detects its base path for local XAMPP installation.

## 3. Database Configuration

The application's main configuration file is:

`config/app.php`

It contains the application settings and database credentials:

- Host: `localhost`
- Database: `aasra_db`
- Username: `root`
- Password: empty by default for a standard local XAMPP setup
- Port: `3306`

If your MySQL credentials are different, update the database configuration in `config/app.php`.

The database connection is handled by `includes/db.php`. An optional `config/local.php` file can override the default database settings when needed.

## 4. Admin Login

The fresh-install SQL file is configured with the following administrator account:

| Field | Value |
|---|---|
| Role | Admin |
| Email | `admin@gmail.com` |
| Password | `12345678` |

Change the default password to a strong, unique password before using the application in a production environment.

**Note:** The fresh-install SQL file does not include the previous demo user and provider accounts. Existing database accounts are not automatically removed unless the database is reset or the SQL file is imported.

## 5. Application Structure

- `index.php` — Main front controller.
- `config/app.php` — Application and database configuration.
- `includes/` — Shared functions, authentication, database connection, routing, and reusable components.
- `includes/routes.php` — Maps clean URLs to application pages.
- `includes/layouts/` — Shared layouts for public pages, authentication, users, providers, and administrators.
- `pages/` — Public pages, authentication pages, dashboards, admin pages, provider pages, user pages, and API handlers.
- `assets/` — CSS, JavaScript, icons, and other frontend assets.
- `uploads/documents/` — Private provider documents.
- `uploads/services/` — Service images.
- `uploads/providers/` — Provider images.
- `database/aasra_db.sql` — Database schema and fresh-install administrator account.
- `.htaccess` — URL rewriting and access restrictions.

## 6. Main Features

### Public Website
- Homepage and public category listings (`/categories`, `/categories/<id>`).
- Categories and informational Helpful Guides cards on the homepage (no separate guide pages).
- Homepage header links (Home, Categories, Guides) scroll smoothly to their sections; from other public pages they return to the matching homepage section.
- Lightweight homepage animations (CSS + `assets/js/motion.js`) that respect `prefers-reduced-motion`.
- Provider discovery based on eligibility and service information.
- Contact and other public information pages.
- Clean URLs without `.php` extensions.

### User Area
- User registration and login.
- User dashboard.
- Access to available providers and services.
- Booking and related account functionality supported by the application.

### Provider Area
- Provider registration and account settings.
- Service and offering management.
- Provider profile and service information.
- Verification status and account status controls.

### Admin Area
- Administrator authentication and dashboard.
- User and provider management.
- Provider verification and approval.
- Service category management.
- Complaint and verification management where available.
- Green-themed administrative sidebar.

### Interface and Usability
- Responsive layout for desktop and mobile.
- Shared page layouts and navigation.
- Clean URL routing.
- Dismissible success and error messages where implemented.
- Public header theme toggle where implemented.

## 7. Roles and Provider Verification

AASRA supports these roles:

- `user`
- `provider`
- `admin`

Provider verification statuses include:

- `Pending`
- `Approved`
- `Rejected`
- `Suspended`

Provider visibility and access depend on the application's verification, account-status, and service-configuration rules.

Administrators manage provider verification and account access through the admin panel.

## 8. Clean URLs

The application uses `.htaccess` and `index.php` for clean URL routing.

Examples:

- `http://localhost/aasra/`
- `http://localhost/aasra/categories`
- `http://localhost/aasra/categories/1` (one category and its providers)
- `http://localhost/aasra/#guides` (the old `/guides` URLs redirect here)
- `http://localhost/aasra/login`
- `http://localhost/aasra/admin`

Actual routes depend on the routes configured in `includes/routes.php`.

Apache must have `mod_rewrite` enabled, and `.htaccess` overrides must be permitted for the project directory.

## 9. Security

The application uses security measures including:

- Password hashing and verification.
- Prepared statements for database queries.
- Output escaping.
- CSRF protection for supported form submissions and AJAX requests.
- Upload validation and randomized filenames.
- Session-based authentication and role checks.
- Access restrictions for private application directories and provider documents.
- Error logging with production error display disabled by default.

Keep database credentials private and configure production security settings before deployment.

## 10. Database and Backups

The `database/` directory contains the current fresh-install SQL file:

`database/aasra_db.sql`

The previous migration SQL files have been removed from the project. Therefore, this README does not provide a migration procedure for older database versions.

**Provider pricing fields migration.** Provider-level Experience, Availability and Charges (`providers.experience`, `providers.availability`, `providers.charges`) have been removed; prices, schedules and experience now live only on each offering (`provider_offerings`). `aasra_db.sql` already reflects this. The one-off migration file for that change is no longer part of this project.

**Services renamed to Categories.** The database now uses `categories` (`category_name`), `provider_categories` and `category_id` (in `provider_categories`, `provider_offerings` and `bookings`); the admin area manages them at `/admin/categories`. Public category browsing now lives at `/categories` and `/categories/<id>`; the old `/services` and `/services?service=<id>` URLs redirect (301) to them, keeping other query parameters. The provider `/provider/services` page is unchanged because it manages the provider's own services (stored in `provider_offerings`). Terminology: **Categories** group and browse providers, **Services** are what a provider offers. `aasra_db.sql` is the fresh-install schema. For an existing database, use only `database/migrations/rename_services_to_categories.sql`: take a `mysqldump` backup first, run its read-only inspection queries, then run the migration (it checks the current state, stops on orphaned rows, never drops tables or deletes rows, and can be re-run). Never import `aasra_db.sql` into an existing database.

**Cash payments.** Payments are cash only with 0% commission: the provider confirms the cash received and an Admin verifies it (Pending → Verification Requested → Paid, or Rejected and resubmitted). For an existing database run `database/migrations/add_cash_payment_workflow.sql` once (it only adds; see `PAYMENT_SETUP.md`).

For an existing installation, preserve the current database and back it up before changing its schema. Do not import the fresh-install SQL file simply to update the application.

## 11. Development Environment

AASRA is built using **PHP, MySQL/MySQLi, HTML, CSS, and JavaScript** and is intended to run locally through XAMPP.

Recommended local URL:

`http://localhost/aasra/`

Use the existing Apache configuration and `.htaccess` routing when working with XAMPP.

---

**Project:** AASRA
**Application type:** PHP / MySQL web application
**Local development environment:** XAMPP
**Database:** `aasra_db`

## Updating an existing installation (cash-confirmation, alerts and Accessibility / Age Category update)

Do **not** import `database/aasra_db.sql` over an existing database; it recreates (empties) the tables.

1. Back up your database (phpMyAdmin > `aasra_db` > Export).
2. Copy the updated project files over your existing project folder (`C:\xampp\htdocs\aasra`). Keep your `uploads/` folder and your `config/app.php` settings.
3. In phpMyAdmin select `aasra_db`, open the **SQL** tab and run `database/migrations/001_add_accessibility_category.sql`. It only adds one nullable column to `users` and is safe to run twice.
4. Optional: if you have payments left in the old `Verification Requested` state, run `database/migrations/002_optional_settle_legacy_cash_confirmations.sql` (read the comments in the file first).
5. Hard-refresh the browser (Ctrl+F5) so the updated `assets/js/app.js` is loaded.

What changed in this update:

- All alerts (flash messages, form errors, notifications) close themselves after 3 seconds; the close button still works. A static note that must stay on screen can opt out with `data-no-autodismiss`. The code is in `assets/js/app.js`.
- Payment IDs on the admin payments page are shown without `#`.
- Admin > Payments is read-only: Verify / Reject are removed, the Status and Actions columns are replaced by one **History** column. A provider's "Confirm cash received" now records the payment as **Paid** and is shown in History.
- The History dropdown is removed from the provider Payments page (history rows stay in the database; the user page keeps its history list).
- Registration, My Profile and Admin > Edit user have an optional **Accessibility / Age Category** field (`users.accessibility_category`, values `disability`, `age_50_plus`, `none`, or NULL = not selected).

## Update: booking address, exact service names, payment filters, provider badge, notifications

**Required for existing databases:** run `database/migrations/005_booking_address_service_name_notifications.sql` once (phpMyAdmin > `aasra_db` > SQL tab). It only adds nullable columns/indexes (`bookings.offering_id`, `bookings.service_name`, `bookings.service_address`, `notifications.related_type/related_id/event_key`), is safe to run twice, and does not touch existing rows (apart from filling the service-name snapshot where the service link already exists). Back up first. Do not import `aasra_db.sql` over existing data. Fresh installs already include the columns.

Until the migration is run, pages that read the new columns will show a database error, so run it before using the updated files.

- Booking page: "Service Location / Booking Address" is prefilled from My Profile and saved per booking (profile address is never changed). Shown to the user, the assigned provider and the admin. Older bookings show "Not provided".
- Provider services are listed newest first (`created_at DESC, id DESC`).
- Provider requests/bookings: long requirements show See More / See Less.
- The exact service name (booking-time snapshot) is shown in bookings, payments, dashboards and notifications, with the category shown separately. Older bookings without a recorded service fall back to the category.
- Provider Payments has server-side filters: status, payment date range, service, booking ID and search.
- Provider sidebar: "Bookings" badge = accepted/confirmed bookings not yet completed, cancelled or rejected (database count for the signed-in provider).
- Notifications (User and Provider only): contextual messages, View Details links, unread marker, no duplicates (`notifications.event_key`). There is no Admin notification feature.

## Update: payment outcome, date of birth / Handicapped sign-up, supporting documents

**Required for existing databases:** run `database/migrations/006_age_handicap_documents_payment_outcome.sql` once (phpMyAdmin > `aasra_db` > SQL tab). It is idempotent and non-destructive: it adds `users.date_of_birth` and `users.is_handicapped` (NULL for existing accounts), creates `user_documents`, and extends the allowed `payment_history.event` values. Back up first. Run `005` as well if you have not already. Do not import `aasra_db.sql` over existing data.

- Payments (Admin, User, Provider): a cancelled or rejected booking shows **Cancelled** / **Rejected** instead of Pending. A real financial state (Paid, Failed) is never overridden, and `payments.payment_status` is never changed by a cancellation or rejection. New cancellations/rejections are written once to the payment history ("User cancelled the service.", "Provider rejected the service."); older ones are shown from the booking's real status.
- Sign-up (regular users): Date of birth (required) and Handicapped Yes/No (required). Allowed only if age is 50+ (calculated on the server) or Handicapped = Yes. Handicapped = Yes requires 1 to 5 documents (PDF/JPG/PNG, up to 5 MB each). Providers' sign-up is unchanged.
- Documents are stored under `uploads/user_documents` (Apache denies direct access) with random names, and are streamed by PHP only to the owner (`/user/documents/ID`) or an administrator (`/admin/user-documents/ID`).
- The old Accessibility / Age Category field is removed from the forms; its column is kept so existing data is not lost.


## Update: complaints, pending providers badge, payments (no database change)

- **Complaints:** a related booking is now required (checked in the browser and on the server; the booking must belong to the user). Complaint records show "Booking #ID" and the service name (never the category) for User, Admin and notifications. The admin response is a normal section under the details; long text has See More / See Less. Older complaints without a booking show "No booking linked".
- **Admin sidebar:** the Providers item shows the live number of providers whose verification status is Pending.
- **Provider Availability page removed** (route `provider/availability`). The optional per-service Availability field in Provider > Services is unchanged.
- **Payments:** User, Provider and Admin pages list only Paid payments and Pending payments of bookings that are not Cancelled/Rejected (`PAYMENT_LIST_SQL` in `includes/payment_helpers.php`). Nothing is deleted. The Provider payments filter was removed; User and Admin filters offer Pending / Paid.
- Hard-refresh the browser (Ctrl+F5) after copying the files so the updated JS/CSS load.
