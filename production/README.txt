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
