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

Cover images accept JPG, PNG, GIF, or WebP up to 5 MB. Published post search uses the posts table's FULLTEXT index. HTTPS should be enabled in production so session cookies are marked secure.
