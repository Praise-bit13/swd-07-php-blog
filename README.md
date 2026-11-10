# SWD Blog CMS

PHP 8 and MySQL blog CMS for `http://localhost/swd/07-php-blog/`.

## Setup

1. For a new installation, create the database and tables by importing `database.sql` in phpMyAdmin or MySQL. If upgrading the original SWD blog tables, back up the database and import `upgrade-database.sql` instead; it widens fields and requires existing posts to have a category.
2. Configure MySQL with environment variables `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` if the defaults (`127.0.0.1`, `swd_blog`, `root`, empty password) are not appropriate.
3. Ensure Apache has `mod_rewrite`/`.htaccess` overrides enabled and PHP's PDO MySQL extension is available. The `app` directory is denied over HTTP; uploads deny PHP execution.
4. Create the first admin from a terminal:

   ```powershell
   C:\xampp\php\php.exe create-admin.php "Your Name" you@example.com
   ```

   Enter a password of at least 12 characters and confirm it. Input is visible in the Windows terminal. The command refuses to create another admin once one exists.
5. Sign in at `/admin/login.php`, create a category, and then create posts. Posts are drafts until published.

Cover images accept JPEG, PNG, or WebP up to 2 MB. Published post search uses the posts table's FULLTEXT index. HTTPS should be enabled in production so session cookies are marked secure.

## Public journal

The public homepage and category pages show six newest published stories per page. Published stories are available at `/post/{slug}` and categories at `/category/{slug}`. Article bodies are HTML-escaped before paragraph and line-break formatting, so stored content is displayed as text rather than executable markup. Article pages show an estimated reading time and up to three related stories from the same category. Search uses the posts FULLTEXT index, with a literal `LIKE` match for queries shorter than three characters.

## Admin CRUD and image-upload protections

The admin can create, edit, filter, and delete posts and categories. Category counts include categories with no posts. Post slugs are generated from titles and get a numeric suffix when a slug is already in use. Publishing a post fills in `published_at` only when it is still empty. Replacing a cover or deleting its post removes the old uploaded image.

Upload checks and the risks they reduce:

- The server checks the uploaded size against 2 MiB to limit storage exhaustion and oversized-upload abuse.
- `is_uploaded_file()` accepts only files PHP received through an HTTP upload, not an arbitrary local path supplied to the upload helper.
- `finfo` inspects the file contents for its real MIME type rather than trusting the browser-supplied filename or `Content-Type`, reducing disguised-file and executable-upload risks.
- Only JPEG, PNG, and WebP are accepted. SVG and other active or unexpected formats are rejected.
- The stored name is generated with `bin2hex(random_bytes(16))` and a MIME-derived extension, preventing attacker-controlled path traversal filenames and reducing collisions.
- Image deletion accepts only generated image filenames, preventing a stored filename from escaping the uploads directory.
- `uploads/.htaccess` disables directory listings and denies PHP-like files as defense in depth if an upload is ever misidentified.
