# Install WhatsJet

This guide is for the person setting up the site. You do not need to be a developer. If a step fails, look at [Common problems](#common-problems) before changing anything else.

WhatsJet does not include a web-based installer. Setup is one command, then a sign-in.

The Envato purchase-code check is still in the product. If the app asks you to activate it, enter the code from your order. Do not look for a way to skip that screen.

## What you need

- PHP **8.2 or newer** (8.3 is a good choice)
- These PHP extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `zip`
- `mysqli` as well, if you want `install.php` to import the database for you. Without it, import `data-schema.sql` in phpMyAdmin.
- MySQL 5.7+ or MariaDB 10.3+, with an **empty database**
- Composer, or permission for PHP to download it
- A domain with HTTPS once you connect WhatsApp (HTTP is enough to sign in the first time on your own computer)

You do **not** need Node.js. CSS and JavaScript are already built.

## Files in the package

| Path | What it is |
| --- | --- |
| `Source/` | The website. Commands below are run inside this folder. |
| `Source/public/` | The folder your domain should point at |
| `Source/.env.example` | Settings template. Setup copies it to `.env`. |
| `Source/install.php` | One-command setup |
| `data-schema.sql` | Empty database tables and the first admin user. It sits next to `Source/`, not inside it. |
| `Installation-Guide.pdf`, `Setup-Guide.pdf` | Original vendor PDFs. Use this file if they disagree. |

`Source/vendor` is not in the download. Setup creates it.

## Fastest way (you have SSH or a hosting terminal)

1. Upload the package and extract it. Keep `data-schema.sql` beside the `Source` folder.
2. In your hosting panel, create an empty MySQL database and a user that can use it. Write down the database name, username, and password.
3. Open a terminal in the `Source` folder:

   ```bash
   cd Source
   cp .env.example .env
   ```

   `cp` is optional. `install.php` copies the file when `.env` is missing.

4. Edit `Source/.env` and set these lines. Leave the others for now.

   ```env
   APP_URL=https://your-domain.com
   DB_DATABASE=the_database_name
   DB_USERNAME=the_database_user
   DB_PASSWORD=the_database_password
   DB_HOST=127.0.0.1
   ```

   `APP_URL` must be the address you type in the browser, with `https://` if the site uses SSL. No slash at the end.

5. Run setup:

   ```bash
   php install.php
   ```

   That command:

   - checks PHP
   - creates `.env` if needed and fills `APP_KEY`
   - downloads dependencies into `vendor/` (`composer install`)
   - creates a **unique** form-encryption key for this server
   - imports `data-schema.sql` when the database connection works and the database is empty

6. Point the domain's document root at `Source/public`.

   On cPanel this is **Domains → document root**. If you cannot change it, put the contents of `Source` in the web folder. The `.htaccess` file in `Source` sends visitors into `public/`. Do not point the domain at the package root (the folder that contains the PDFs).

7. Open the site and sign in:

   - Email: `superadmin@yourdomain.com`
   - Password: `firstadmin123`

   Change that password right away (**user menu → profile**).

8. If you land on **Licence information**, enter the Envato purchase code from your order and your email. That screen is supposed to be there.

9. Add a cron job so campaigns can send. Every minute:

   ```bash
   php /full/path/to/Source/artisan schedule:run
   ```

   If the host only allows a URL cron, call this every minute:

   ```text
   https://your-domain.com/run-cron-schedule
   ```

10. Connect WhatsApp later, from the vendor settings, after the admin login works. That needs a public HTTPS address.

## If you cannot use a terminal

1. Create the empty database.
2. In phpMyAdmin, select that database and **Import** `data-schema.sql`.
3. Copy `Source/.env.example` to `Source/.env`.
4. Edit `.env` as in step 4 above. Also set `APP_KEY`. You can generate one on any computer with PHP:

   ```bash
   php -r "echo 'base64:'.base64_encode(random_bytes(32)), PHP_EOL;"
   ```

   Paste the result after `APP_KEY=`.
5. Ask the host to run, inside `Source`:

   ```bash
   composer install --no-dev --prefer-dist --optimize-autoloader
   php scripts/regenerate-form-keys.php
   ```

   Or upload after running those two commands on your computer, including the new `vendor` folder and `storage/app/form-encryption-keys.php`. The key file must be created **on the server you deploy to**, or copied with the site and then regenerated. Do not reuse one key across unrelated sites. See [Form encryption key](#form-encryption-key).
6. Point the domain at `Source/public` and sign in as above.

## Form encryption key

`Source/app/Yantrana/__Laraware/Config/tech-config.php` contains a default RSA private key. Everyone who downloads the package has that same key. **Do not run a real site on it.**

`php install.php` writes a new key the first time, into:

```text
Source/storage/app/form-encryption-keys.php
```

That file is not in git. The app uses it instead of the shared key. The first page view also creates it if the file is missing and OpenSSL works.

On a new server, or any time you want to rotate the key:

```bash
cd Source
php scripts/regenerate-form-keys.php
```

The command prints a short fingerprint so you can see that a new key exists. It does not print the private key. People with a form already open should reload the page.

Do not paste the new private key back into `tech-config.php`, and do not commit `form-encryption-keys.php`.

## What to edit in `.env`

Only the first block matters for a first run.

| Setting | Typical value | Why |
| --- | --- | --- |
| `APP_URL` | `https://your-domain.com` | Links and webhooks |
| `APP_DEBUG` | `false` | `true` shows errors on screen. Turn it on only while fixing a problem, then turn it off. |
| `APP_KEY` | filled by setup | Leave it after setup. Changing it later makes saved encrypted settings unreadable. |
| `DB_*` | your database | Required |
| `MAIL_MAILER` | `log` at first | Password emails are written to `storage/logs` until you set SMTP |
| `QUEUE_CONNECTION` | `sync` | Fine for the first run. Campaigns still need the cron job above. |
| `FORCE_HTTPS` | `true` when the site has SSL | Stops mixed-content redirects |
| `IS_DEMO_MODE` | `false` | `true` locks the site into demo mode |
| `REDIS_CLIENT` | `predis` | Matches what Composer installs. Change only if you installed the phpredis extension. |

WhatsApp, Stripe, and other API keys are entered in the admin panel after login. You can leave them empty now.

## Folder permissions

PHP must be able to write to:

- `Source/storage` (and everything under it)
- `Source/bootstrap/cache`
- `Source/public/media-storage`

If pages fail with "permission denied" or logs are not created, set those folders to `775`.

## Common problems

**The site says "WhatsJet is not installed yet"**  
`vendor/` was not created. From `Source`, run `php install.php`.

**The site says "Configuration file missing"**  
`Source/.env` does not exist. Copy `.env.example` to `.env`, or run `php install.php`.

**`composer install` says the PHP version is too old**  
The host is below PHP 8.2. Switch the domain's PHP version in the hosting panel, then run setup again.

**`composer install` stops on `unn/gettext-blade` or "not stable enough"**  
Use the `composer.json` and `composer.lock` from this package (the requirement is `dev-main@dev`) and run `php install.php` again. Do not run `composer update` unless you know you need newer libraries.

**`composer install` says `dompdf` is blocked by a security advisory**  
Current Composer refuses `dompdf` 3.1.0. This package pins 3.1.6, which Composer accepts. Use the `composer.json` and `composer.lock` included here.

**Composer says `box/spout` is abandoned**  
That is a notice, not a failure. Setup can continue.

**`composer install` runs out of memory**  
Run `COMPOSER_MEMORY_LIMIT=-1 php install.php`. Setup already sets that limit when it calls Composer.

**Could not create a unique form encryption key**  
The `openssl` extension is missing, or OpenSSL has no config file. Install `php-openssl`. On XAMPP, set `OPENSSL_CONF` to `C:\xampp\apache\conf\openssl.cnf` and run `php scripts/regenerate-form-keys.php`.

**Database import failed, or the site shows a SQL error**  
Confirm the database exists, the user can access it, and `DB_*` in `.env` matches. If a previous import stopped halfway, drop the tables (or create a new empty database) and run `php install.php` again. You can also import `data-schema.sql` yourself in phpMyAdmin.

**Login page loads but sign-in fails**  
The `users` table is missing or the import did not finish. Import `data-schema.sql`. The first account is `superadmin@yourdomain.com` / `firstadmin123`.

**Blank page**  
Set `APP_DEBUG=true` in `.env`, reload, read the message, fix it, then set `APP_DEBUG=false`. Also check `storage/logs/laravel.log`. A blank page is often a folder PHP cannot write to.

**CSS looks unstyled, or you see a list of files**  
The domain is not pointed at `Source/public`. Change the document root. `mod_rewrite` must be enabled (Apache). On Nginx, send all requests that are not real files to `index.php`.

**500 error right after login, or "please verify the licence"**  
Enter the purchase code from your Envato downloads page. The licence screen is part of the app.

**Campaigns stay queued**  
The cron job from step 9 is not running. `QUEUE_CONNECTION=sync` does not replace that cron job.

**Changed `.env` and nothing happened**  
Restart PHP (or the queue worker, if you added one). From `Source` you can run:

```bash
php artisan config:clear
```

Do not run `php artisan config:cache` until `.env` is final.

## Updating later

Take a backup of `.env`, `storage/app/form-encryption-keys.php`, and `public/media-storage` before replacing files. Do not overwrite those with the new package. Follow the notes in `Upgrade-Guides/` for the version you are moving to, then run `composer install` again inside `Source`.
