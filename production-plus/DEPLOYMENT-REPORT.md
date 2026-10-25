# Deployment Readiness Report

Review date: 2026-10-24

## Repository packages

- `production/` contains the clean deployment source and schema-only installer.
- `demo/` contains a separate full source copy with a synthetic seed for three
  example posts. Its schema creates `pinkcms_demo` and contains no users,
  activity, real credentials, or private local content.
- Root `.gitignore` rules exclude `config.php`, uploads, `.env` files, and
  populated local `database.sql` dumps from both packages.

## Prepared

- Removed the public-header shortcut to the admin login. Authorized admins can
  still use `/admin/` directly.
- Added an allowlisted admin file manager for `robots.txt` and `ads.txt`. The
  content is stored in MySQL, limited to 20 KB per file, and retains up to
  three prior versions for restoration. It does not expose arbitrary server
  paths or allow editing PHP/configuration/system files.
- Added XML sitemap index and sitemap endpoints for published posts/pages,
  taxonomy entries, and static archives. Sitemap files are capped at 5,000
  URLs each and are routed through Apache rewrite rules.
- Disabled search indexing by default; public pages emit `noindex` and the
  managed `robots.txt` blocks crawlers until indexing is explicitly enabled.
- Added admin guidance for verifying the site in Google Search Console and
  manually submitting `sitemap_index.xml`; no Google credentials or automatic
  submission integration is used.
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
6. In the File Manager and Sitemap screen, set the canonical HTTPS URL. Keep
   indexing disabled until public launch. `robots.txt` is a crawler directive,
   not a security boundary; use host-level authentication if the site is
   private.
7. At launch, enable indexing, verify domain ownership in Google Search
   Console, and submit `sitemap_index.xml` from its Sitemaps page.

Pushing to GitHub alone does not deploy this folder to hosting. Configure an
approved deployment integration or upload the reviewed `production-plus`
release through the host's normal deployment process.

## Deliberately not published

The local `database.sql` is a populated development dump. It includes an
administrator account created with a weak default password and sample site
data. It is ignored by Git and must not be uploaded or deployed. The public
demo uses synthetic records instead. Use the schema-only installer for a clean
installation. Local `config.php` and uploaded media also remain untracked.

## Validation limits

This was a source/configuration readiness pass, not a production deployment.
No production credentials, domain, hosting configuration, backups, or external
service credentials were supplied, so those items still require verification
on the target server.
