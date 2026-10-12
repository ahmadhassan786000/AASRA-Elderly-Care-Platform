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
- Homepage and public service listings.
- Service categories and guides.
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
- `http://localhost/aasra/services`
- `http://localhost/aasra/guides`
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
