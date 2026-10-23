PINKCMS PHP + MySQL APPLICATION
===============================

This folder contains the PHP frontend and 15-menu admin panel.

For a public checkout, copy `config.example.php` to `config.php` and set the
`PINKCMS_DB_HOST`, `PINKCMS_DB_USER`, `PINKCMS_DB_PASS`, `PINKCMS_DB_NAME`, and
`PINKCMS_DB_PORT` environment variables. Local configuration, uploaded media,
and the populated local database dump are intentionally excluded from source
control.

Local Windows URLs:
  Frontend: http://localhost/pinkcmswebstie/pinkcms-php/
  Admin:    http://localhost/pinkcmswebstie/pinkcms-php/admin/

From the project root, run `start-local.ps1` to start Apache, start the isolated
MariaDB instance on port 3308, create the database and open the frontend.
The local database files are stored in `C:\xampp\mysql\data-pinkcms`, separate
from any other database already present in XAMPP.

Local sample administrator credentials are excluded from this public repository.
Create a unique, strong administrator password for your own installation before
deployment.

Admin > SEO lets you set the website title, meta description, primary and
secondary keywords, review page/post publication states, and see a live SEO
readiness score. The public site has a paginated all-posts page
(`?page=all`), a category directory (`?page=categories`), and individual
category archives (`?page=category&slug=...`). Category archives use published
post categories and assigned tags, so existing tagged posts appear without
changing the database. These public archive links are included in the admin SEO
URL list, which uses the current website host and installation path.
Admin > Posts shows total posts and counts by status, and offers a standard
table, compact image rows, and responsive detail cards. Detail cards display
category, studio, actors, and tags as unlabeled capsules. The selected view is
remembered in the browser. A collapsible keyword-notes panel below the counts
stores editable primary and secondary keyword lists separately from post
content; the notes open in a full-screen panel, keyword chips copy only the
keyword text, and editing fields appear only after choosing Edit. These notes
are not automatically added to posts.
Video duration is entered in separate hour, minute and second fields; typing a
colon advances to the next field, and existing numeric/legacy durations are
normalized to HH:MM:SS. The post editor has searchable actor and tag pickers with
usage counts, the ten most-used recommendations, removable selected chips, and
support for adding new names by pressing Enter. Category and studio fields show
matching existing names while typing; existing names can be selected, and new
names can be entered and added with Enter. Frontend banners use an article
carousel with a title, excerpt, optional code and cover image, previous/next
controls, slide dots, swipe support, and three-second rotation. When a banner
links to a published post and has no image of its own, the post poster or cover
is used automatically. Public pages share the same top navigation and mobile
navigation; post sharing uses labeled, color-coded social icons.
Post-editor text and file fields include clear buttons that update previews and
the live SEO checks immediately.

The post editor keeps all fields in one form and places the live SEO checklist
and post preview beside the cover/poster and video controls on wide screens;
these blocks become a single column on smaller screens. Post-specific SEO fields
follow the video preview. Code and Focus Keyphrase appear together, while
category, studio, actor and tag pickers share one block with tags last. The live
checklist reviews title and meta lengths (70 and 140
characters), keyphrase, slug, content length and paragraphing, inline image alt
text, and content links. Empty Meta Description values use up to 140 characters
of post content. These checks are editorial guidance, not a ranking guarantee.
Public post details link assigned categories, studios, actors and tags to their
corresponding site archives; adding redundant automatic links inside post prose
is not required.
The editor aligns the basic post fields beside a sticky, collapsible SEO
recommendation box at the top of the desktop layout. The post preview itself
scrolls normally below the recommendation box; the selected cover preview and
media fields follow it, while the main content fields flow in the adjacent
column. Existing categories are searchable and listed as usage-count
recommendations, while new names can still be typed.
The columns stack on narrow screens.

Admin post cards show the author and creation date/time in the Asia/Bangkok
timezone, followed by category, studio, actors and tags in that order. New posts
store the authenticated admin username and Thai-local creation time. Existing
posts without historical author/time data display "ไม่ระบุ" for author and retain
their stored date while marking the unavailable time, rather than inventing
values. For an older database, back up first and run the additional
`author` and `createdAt` column statements at the end of `database-upgrade.sql`.

Video embeds accept a direct video URL or a complete iframe snippet; PinkCMS
extracts and validates only its `src` rather than rendering pasted HTML. The
supported player URLs include YouTube, Google Drive, Fembed, javzero, and ggcdn.
For the optional Vdohide remote conversion API, configure your own
`PINKCMS_VDOHIDE_TOKEN` as an Apache/PHP server environment variable and restart
Apache. The token is never placed in the page or database. Without it, supported
video URLs and iframes continue to play directly. An API failure is logged and
shown in the admin notice while preserving the original player link.

Published video posts output VideoObject JSON-LD, canonical and Open Graph
metadata using the post's title, description, duration, cover and player URL.

Images can be uploaded from the post cover, studio/actor image, banner, and
Media Library forms, or provided as an http(s) image URL. Uploads accept JPG,
PNG, GIF, and WebP up to 5 MB and are stored in `pinkcms-php/uploads`; ensure
this folder is writable by the web server on hosting. Configure PHP's
`upload_max_filesize` to at least 5 MB and `post_max_size` high enough for the
total selected gallery batch plus form data.
The post editor gallery supports multiple image uploads as well as one image URL
per line, shows a four-column preview, and appends new uploads to existing
gallery images. Published post galleries display four columns on desktop, three
on tablet-sized screens, and two on mobile. Visitors can horizontally browse
the gallery using edge controls or keyboard arrows, then navigate the image
viewer with arrows and close it with Escape. The copy-link icon is grouped with
the other share icons on mobile and desktop.
The post editor also supports a separate poster image by upload or URL. When no
poster is set, the single-post page falls back to the cover, then the video
thumbnail. The page shows linked studio, actor, and tag details plus copy/share
actions; selecting the poster opens it in the image viewer. Existing databases
need the `posterUrl` column from `database-upgrade.sql` (back up first).

Apache access to setup files, SQL files, and internal PHP include files is
blocked by the included .htaccess files. The admin panel also sends no-cache
and no-index headers. Keep this project behind localhost or a properly secured
production web server; these protections do not replace changing the password,
HTTPS, or least-privilege database credentials.

Database setup:
  `database.sql` is a local-only populated dump and is intentionally not
  published. `database-upgrade.sql` adds post fields to an older installation;
  back up first and run it once only when upgrading.

For production, edit `config.php` with a dedicated least-privilege database
account and production credentials. Configure HTTPS, backups, and hosting
permissions; never expose the local XAMPP setup directly to the internet.
