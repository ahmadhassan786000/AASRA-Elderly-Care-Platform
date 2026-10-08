# AASRA — PHP / MySQL (v2)

Connecting Care, Building Trust. Public website, User area, Provider area and Admin area.

## Setup (XAMPP)
1. Copy this folder to `htdocs/aasra` and start **Apache** + **MySQL**. Apache needs `mod_rewrite` (on by default) and `AllowOverride All` for htdocs (XAMPP default) so `.htaccess` works.
2. In phpMyAdmin import `database/aasra_db.sql` (fresh install, includes demo data).
   *Already have the v1 database?* Back it up, then import `database/migration_v2.sql` instead (run once). It converts provider accounts to the new `provider` role and keeps your data.
3. Edit `config/database.php` if your MySQL credentials differ.
4. Open `http://localhost/aasra/`. The base path is detected automatically, so it also works at a domain root.

No Apache? `php -S localhost:8000 router_dev.php` serves the app with the same clean URLs.

## Demo logins
| Role | Email | Password |
|---|---|---|
| Admin | admin@aasra.com | Admin@123 |
| User | user@aasra.com | User@123 |
| Provider | provider@aasra.com | Provider@123 |

## How it is organised
- `index.php` – front controller; `includes/routes.php` – every clean URL → page file in `pages/`.
- `pages/` – public, auth, user, provider, admin, api. `includes/layouts/` – five separate layouts (public, auth, user top-nav, provider sidebar, admin green sidebar).
- `.htaccess` – rewrites everything to `index.php`; blocks `config/ includes/ pages/ database/`. Old `.php` URLs 301-redirect to the clean URL or show the 404 page.
- `uploads/documents/` is private (served only by PHP to admins / the owning provider). `uploads/services/` and `uploads/providers/` are public images.

## Behaviour notes
- **Roles**: `users.role` is `user | provider | admin`; login redirects to `/user`, `/provider`, `/admin`. Account status is re-checked on every protected request, so deactivating an account takes effect immediately.
- **Provider lifecycle**: registration stores only name/email/password; provider fields stay `NULL` until completed in *Account Settings*. A provider appears to users only when verification is **Approved**, the account is **Active** and charges are set.
- **Verification actions**: *Approve / Activate* → Approved + Active. *Inactive / Reject* → Rejected (hidden from users, can still log in to fix and re-upload). Use *Deactivate* on the Providers page to block login.
- **Security**: prepared statements everywhere, escaped output, CSRF tokens on all POSTs and AJAX, `password_hash`, validated uploads (finfo MIME + size + image dimensions, random filenames), errors logged not shown.
- Removed: admin Reports page, Quick Actions. The v1 sidebar linked to a non-existent `compare.php`; that link is gone.
