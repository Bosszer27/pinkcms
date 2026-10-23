# Deployment Readiness Report

Review date: 2026-10-24

## Prepared

- Removed the public-header shortcut to the admin login. Authorized admins can
  still use `/admin/` directly.
- Added a schema-only database installer at `database-schema.sql`. It creates
  the application tables without accounts, site content, settings, activity
  history, or uploaded media.
- Kept local configuration, uploads, and the populated `database.sql` dump
  excluded from Git.
- Added configurable site-content/menu settings separately from the main SEO
  settings.
- Added responsive admin navigation for small screens.
- PHP syntax checks and `git diff --check` passed for the changed PHP files.

## Required before production

1. Install `database-schema.sql` into a new database.
2. Create the first administrator using a unique password hash generated with
   PHP `password_hash()`; do not reuse the local default account.
3. Copy `config.example.php` to `config.php` and provide production database
   credentials through server environment variables. Use a dedicated
   least-privilege database account.
4. Enable HTTPS, verify Apache/PHP configuration and required extensions, and
   grant write access only to the uploads directory.
5. Configure backups, upload/request limits, error logging, and hosting access
   controls; verify the site and admin on the production host before launch.

## Deliberately not published

The local `database.sql` is a populated development dump. It includes an
administrator account created with the weak default password `admin123` and
sample site data. It is ignored by Git and must not be uploaded or deployed.
Use the schema-only installer for a clean installation. Local `config.php`
and uploaded media also remain untracked.

## Validation limits

This was a source/configuration readiness pass, not a production deployment.
No production credentials, domain, hosting configuration, backups, or external
service credentials were supplied, so those items still require verification
on the target server.
