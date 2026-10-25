PINKCMS PRODUCTION PACKAGE
==========================

This folder is the clean, server-ready application source. The database schema
is empty: it creates tables but does not add users, posts, settings, or media.

1. Copy this folder's contents to the web root (or a dedicated subdirectory).
2. Create a MySQL/MariaDB database with a database administrator account.
3. Import `database-schema.sql`; then grant a separate least-privilege
   application user access only to this database.
4. Copy `config.example.php` to `config.php` and set:
   PINKCMS_DB_HOST, PINKCMS_DB_USER, PINKCMS_DB_PASS, PINKCMS_DB_NAME,
   and PINKCMS_DB_PORT as server environment variables.
5. Create the first administrator with a unique password hash produced by
   PHP `password_hash()`. Do not reuse a sample or development password.
6. Ensure only the uploads directory is writable by PHP. Enable HTTPS,
   configure backups and error logging, then verify the site and admin.

The SQL schema defaults to a database named `pinkcms`. If you use another
database name, change the CREATE DATABASE and USE lines before importing.
`database-upgrade.sql` is only for an existing older installation; back up
first and apply only the missing columns.

The project requires PHP with mysqli and mbstring, MySQL/MariaDB, and Apache
with mod_rewrite for the included clean URLs and file access rules.
See `DEPLOYMENT-REPORT.md` for the remaining production checks.

FILE MANAGER, INDEXING, AND SITEMAP
-----------------------------------

The admin menu "File Manager and Sitemap" manages only robots.txt and ads.txt.
Their contents, along with up to three previous versions per file, are stored
in the database. This is not a general-purpose server file editor; PHP, config,
SQL, .htaccess, and other system files cannot be edited from this screen.

Search indexing is disabled by default. While disabled, robots.txt blocks
crawlers and public pages send noindex. This is a crawler directive, not an
access-control feature; use hosting authentication or maintenance protection
if the site must not be publicly accessible.

The sitemap index is available at /sitemap_index.xml. It includes published
posts and pages, public taxonomy entries, and static archive routes, with no
more than 5,000 URLs per sitemap file. Configure the canonical HTTPS site URL
in the admin screen. Once the site is ready, enable indexing, verify ownership
in Google Search Console, then submit sitemap_index.xml in the Sitemaps section.
This application generates the sitemap but does not connect to Google or submit
it automatically.

Pushing this source to GitHub does not update a hosting server unless a
deployment integration is separately configured. Review and deploy the
production-plus folder to the intended host using your normal release process.
