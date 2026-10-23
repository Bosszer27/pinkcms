PINKCMS DEMO PACKAGE
====================

This is a complete copy of the application with synthetic example records.
The demo database name is `pinkcms_demo`. It contains no real accounts,
credentials, user activity, or private content; the users table starts empty.
The sample posts are fictional and do not include real video URLs.

1. Create a MySQL/MariaDB database, or use the database name `pinkcms_demo`.
2. Import `database-schema.sql`.
3. Import `database-demo-seed.sql` into that same database.
4. Copy `config.example.php` to `config.php` and configure the database
   environment variables (the default database name is `pinkcms_demo`).
5. Open the demo frontend. To access the admin, create your own administrator
   with a unique password hash using PHP `password_hash()`.

The demo SQL contains synthetic names, settings, and posts only. It must not be
used as a production database. For a clean hosting installation, use the
separate `production/` package and its empty schema.
