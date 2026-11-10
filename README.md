# SWD Journal CMS

A PHP 8 and MySQL blog CMS for a local XAMPP installation at:

`http://localhost/swd/07-php-blog/`

## Features

- Responsive public journal with article cards, six-post pagination, category pages, search, and clean `/post/{slug}` and `/category/{slug}` URLs.
- Article reading-time estimates, related stories, and safe paragraph rendering that escapes database content instead of rendering stored HTML.
- MySQL FULLTEXT search, with a literal `LIKE` fallback for short queries.
- Admin sign-in with password hashing, hardened sessions, CSRF protection, post and category CRUD, status filtering, and a dashboard.
- Cover image uploads limited to 2 MiB JPEG, PNG, and WebP files, with server-side MIME checks and random filenames.
- First-admin creation and a repeat-safe CLI seeder for 12 sample posts.

## Screenshots

Screenshots are not included yet. To capture the current UI, open the local site in a browser and save screenshots into a `screenshots/` folder. Useful screens to capture:

- Public homepage with the latest stories.
- An article page and its related stories.
- Admin dashboard and post editor.

## Setup on XAMPP

1. Put the project folder at:

   `C:\xampp\htdocs\swd\07-php-blog`

2. Start **Apache** and **MySQL** from the XAMPP Control Panel. Confirm that Apache overrides are enabled so the project `.htaccess` rewrite rules are applied.

3. Open `http://localhost/phpmyadmin/`, select **Import**, choose `database.sql`, and run the import. This creates the `swd_blog` database and its tables, including the post FULLTEXT index. If you are upgrading the original SWD blog tables instead, back up first and use `upgrade-database.sql`.

4. The default local MySQL connection is `127.0.0.1`, database `swd_blog`, user `root`, and an empty password. If your MySQL credentials differ, set `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` in the PHP/Apache environment.

5. Create the first admin in PowerShell or Command Prompt:

   ```powershell
   cd C:\xampp\htdocs\swd\07-php-blog
   C:\xampp\php\php.exe create-admin.php "Your Name" you@example.com
   ```

   Enter and confirm a password of at least 12 characters. Password input is visible in the terminal. The command only creates an admin when no admin account exists.

6. Visit `http://localhost/swd/07-php-blog/` for the public site or `http://localhost/swd/07-php-blog/admin/login.php` to sign in.

7. Optional: populate a new or empty blog with 12 sample posts:

   ```powershell
   C:\xampp\php\php.exe seed.php
   ```

   The seeder creates the Tutorials, News, and Career categories if needed. Re-running it skips sample posts that are already present.

## Export the database from phpMyAdmin

To export the current database as `database.sql`:

1. Open `http://localhost/phpmyadmin/` and select **swd_blog** in the left sidebar.
2. Select the **Export** tab.
3. Choose **Quick** export and **SQL** format, then click **Export** (or **Go**) to download the `.sql` file.
4. Save the download as `C:\xampp\htdocs\swd\07-php-blog\database.sql`, replacing the setup schema only if that is what you intend. For a backup, keep the setup file and save it under a dated name such as `swd_blog-backup-2026-11-10.sql`.

An export contains private site data, including admin email addresses and password hashes. Store it securely and do not publish it.

## Folder structure

```text
07-php-blog/
|-- admin/
|   |-- categories/       Category list, create, edit, and delete
|   |-- posts/            Post list, filters, create, edit, and delete
|   |-- header.php        Protected admin shell and navigation
|   |-- index.php         Dashboard and SQL summaries
|   |-- login.php
|   `-- logout.php
|-- app/
|   |-- bootstrap.php     Session and application bootstrap
|   |-- db.php            PDO/MySQL connection
|   |-- helpers.php       Escaping, URLs, slugs, and upload helpers
|   |-- auth.php
|   |-- csrf.php
|   |-- public-header.php Shared public layout
|   |-- public-footer.php
|   `-- .htaccess         Deny direct web access
|-- assets/
|   |-- site.css          Public site styling
|   `-- style.css         Admin styling
|-- uploads/              Cover images; PHP execution denied
|-- category.php
|-- create-admin.php      CLI-only first admin setup
|-- database.sql          Fresh installation schema
|-- index.php             Public homepage
|-- post.php              Public article
|-- search.php
|-- seed.php              CLI-only sample data seeder
|-- upgrade-database.sql  Migration from original SWD tables
`-- .htaccess             Pretty public URLs
```

## Image-upload protections

Uploads are limited to 2 MiB, must be HTTP-uploaded files, and are checked with `finfo` against an image MIME allowlist. A cryptographically random filename and MIME-derived extension are used instead of the client filename. Replaced/deleted images are removed, stored filenames are validated before deletion, and `uploads/.htaccess` disables directory listings and denies PHP-like files.

For production, use HTTPS and a dedicated MySQL account with only the permissions the app requires.
