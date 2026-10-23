PINKCMS PHP + MYSQL
===================

This repository contains two separate application packages:

  demo/       Complete example site with synthetic sample database records.
  production/ Clean source and an empty schema for a new server installation.

Start with the production package for hosting. Follow the setup steps in
`production/README.txt`; the deployment checklist is in
`production/DEPLOYMENT-REPORT.md`.

To preview the example package, follow `demo/README.txt`. Its database seed
contains fictional sample records only. Neither package includes an admin
account; create your own strong password hash with PHP `password_hash()`.

The local populated database dump, `config.php`, `.env` files, and uploaded
media are excluded from Git. Never publish or deploy the local development
database dump.
