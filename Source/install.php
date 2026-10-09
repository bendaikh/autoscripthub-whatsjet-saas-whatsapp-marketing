<?php

/**
 * WhatsJet first-time setup.
 *
 * From the Source folder:
 *     php install.php
 *
 * What it does:
 *   1. Checks PHP version and extensions
 *   2. Copies .env.example to .env when .env is missing
 *   3. Fills APP_KEY when it is empty
 *   4. Runs composer install (downloads the vendor folder)
 *   5. Creates a unique form-encryption key for this server
 *   6. Makes storage and cache folders writable
 *   7. Imports data-schema.sql when the database is reachable and empty
 *
 * Purchase-code / licence checks are not changed.
 * Flags: --skip-schema   --force-keys
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run this from the command line: php install.php\n");
    exit(1);
}

$root = __DIR__;
chdir($root);

$skipSchema = in_array('--skip-schema', $argv, true);
$forceKeys = in_array('--force-keys', $argv, true);
$failed = false;

function install_say(string $message): void
{
    echo $message."\n";
}

function install_fail(string $message): void
{
    fwrite(STDERR, "ERROR: ".$message."\n");
}

install_say('WhatsJet setup');
install_say('------------');

if (PHP_VERSION_ID < 80200) {
    install_fail('PHP 8.2 or newer is required. This server has PHP '.PHP_VERSION.'.');
    exit(1);
}
install_say('PHP '.PHP_VERSION.' is new enough.');

$requiredExtensions = [
    'bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'gd', 'intl',
    'mbstring', 'openssl', 'pdo', 'pdo_mysql', 'tokenizer', 'xml', 'zip',
];
$missingExtensions = array_values(array_filter($requiredExtensions, function ($extension) {
    return ! extension_loaded($extension);
}));
if ($missingExtensions) {
    install_fail('Install these PHP extensions, then run this again: '.implode(', ', $missingExtensions));
    exit(1);
}
install_say('Required PHP extensions are installed.');

$envExample = $root.'/.env.example';
$envFile = $root.'/.env';
if (! is_file($envExample)) {
    install_fail('.env.example is missing from the Source folder.');
    exit(1);
}
if (! is_file($envFile)) {
    if (! copy($envExample, $envFile)) {
        install_fail('Could not copy .env.example to .env.');
        exit(1);
    }
    install_say('Created .env from .env.example.');
} else {
    install_say('.env already exists. Left it as-is.');
}

$envText = (string) file_get_contents($envFile);
if (! preg_match('/^APP_KEY=(.+)$/m', $envText, $keyMatch) || trim($keyMatch[1]) === '') {
    $appKey = 'base64:'.base64_encode(random_bytes(32));
    if (preg_match('/^APP_KEY=.*$/m', $envText)) {
        $envText = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$appKey, $envText, 1);
    } else {
        $envText = "APP_KEY={$appKey}\n".$envText;
    }
    if (file_put_contents($envFile, $envText) === false) {
        install_fail('Could not write APP_KEY into .env.');
        exit(1);
    }
    install_say('Generated APP_KEY in .env.');
} else {
    install_say('APP_KEY is already set.');
}

$writableDirs = [
    'storage',
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache',
    'public/media-storage',
];
foreach ($writableDirs as $relative) {
    $path = $root.'/'.$relative;
    if (! is_dir($path) && ! mkdir($path, 0775, true) && ! is_dir($path)) {
        install_fail('Could not create '.$relative);
        $failed = true;
        continue;
    }
    if (! is_writable($path)) {
        @chmod($path, 0775);
    }
    if (! is_writable($path)) {
        install_fail($relative.' is not writable by PHP. On shared hosting, set this folder to 775.');
        $failed = true;
    }
}
if (! $failed) {
    install_say('Storage and cache folders are writable.');
}

putenv('COMPOSER_MEMORY_LIMIT=-1');
$composer = install_find_composer($root);
if ($composer === null) {
    install_fail('Composer is not installed and could not be downloaded. Install it from https://getcomposer.org/download/ and run this again.');
    exit(1);
}

install_say('Installing PHP dependencies (this can take a few minutes)...');
$composerCommand = $composer.' install --no-dev --prefer-dist --no-interaction --optimize-autoloader';
passthru($composerCommand, $composerExit);
if ($composerExit !== 0 || ! is_file($root.'/vendor/autoload.php')) {
    install_fail('composer install failed. Read the messages above. See INSTALL.md, section "Common problems".');
    exit(1);
}
install_say('Dependencies installed in vendor/.');

require_once $root.'/app/Yantrana/Support/form-encryption-keys.php';
try {
    $keys = whatsjetEnsureFormEncryptionKeys($forceKeys);
    $fingerprint = whatsjetFormEncryptionKeysFingerprint($keys);
    install_say('Unique form encryption key is ready (fingerprint '.$fingerprint.').');
    install_say('It lives in storage/app/form-encryption-keys.php and is not part of the download.');
} catch (Throwable $exception) {
    install_fail('Could not create a unique form encryption key: '.$exception->getMessage());
    install_fail('Fix OpenSSL, then run: php scripts/regenerate-form-keys.php');
    $failed = true;
}

if ($skipSchema) {
    install_say('Skipped the database import (--skip-schema).');
} else {
    $imported = install_import_schema($root, $envFile);
    if ($imported === false) {
        $failed = true;
    }
}

install_say('');
if ($failed) {
    install_say('Setup finished with problems. Fix the ERROR lines above, then run php install.php again.');
    exit(1);
}

install_say('Setup finished.');
install_say('Next:');
install_say('  1. In .env, set APP_URL to the real website address (https://your-domain).');
install_say('  2. Point the domain at the Source/public folder.');
install_say('     If you cannot change the document root, the Source/.htaccess file forwards requests into public/.');
install_say('  3. Open the site and sign in:');
install_say('       email    superadmin@yourdomain.com');
install_say('       password firstadmin123');
install_say('     Change that password immediately.');
install_say('  4. If the app asks for a purchase code, enter the Envato purchase code from your order.');
install_say('     That check stays in place.');
install_say('  5. Add a cron job every minute:');
install_say('       php '. $root.'/artisan schedule:run');
install_say('     Or call https://your-domain/run-cron-schedule every minute.');
install_say('More detail is in INSTALL.md.');
exit(0);

function install_find_composer(string $root): ?string
{
    $existing = trim((string) shell_exec('command -v composer 2>/dev/null'));
    if ($existing !== '' && is_file($existing)) {
        return escapeshellarg($existing);
    }

    foreach (['/usr/local/bin/composer', '/usr/bin/composer'] as $candidate) {
        if (is_file($candidate)) {
            return escapeshellarg($candidate);
        }
    }

    $phar = $root.'/composer.phar';
    if (! is_file($phar)) {
        install_say('Composer was not found. Downloading composer.phar...');
        $installer = $root.'/composer-setup.php';
        $installerBytes = @file_get_contents('https://getcomposer.org/installer');
        if ($installerBytes === false) {
            return null;
        }
        file_put_contents($installer, $installerBytes);
        passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg($installer).' --install-dir='.escapeshellarg($root).' --filename=composer.phar', $exitCode);
        @unlink($installer);
        if ($exitCode !== 0 || ! is_file($phar)) {
            return null;
        }
    }

    return escapeshellarg(PHP_BINARY).' '.escapeshellarg($phar);
}

/**
 * @return bool|null true imported or already present, null skipped (not a failure), false failure
 */
function install_import_schema(string $root, string $envFile): ?bool
{
    $schema = null;
    foreach ([$root.'/../data-schema.sql', $root.'/data-schema.sql'] as $candidate) {
        if (is_file($candidate)) {
            $schema = $candidate;
            break;
        }
    }
    if ($schema === null) {
        install_fail('data-schema.sql was not found next to the Source folder. Import it with phpMyAdmin.');
        return false;
    }

    $env = install_read_env($envFile);
    $host = $env['DB_HOST'] ?? '127.0.0.1';
    $port = (int) ($env['DB_PORT'] ?? 3306);
    $database = $env['DB_DATABASE'] ?? '';
    $username = $env['DB_USERNAME'] ?? '';
    $password = $env['DB_PASSWORD'] ?? '';

    if ($database === '' || $username === '') {
        install_say('Database name or username is empty in .env. Import data-schema.sql after you fill those in, then run php install.php again.');
        return null;
    }

    if (! extension_loaded('mysqli')) {
        install_say('The mysqli PHP extension is not installed, so this script cannot import the database.');
        install_say('Import '.$schema.' with phpMyAdmin, then reload the site.');
        return null;
    }

    mysqli_report(MYSQLI_REPORT_OFF);
    $db = @new mysqli($host, $username, $password, $database, $port);
    if ($db->connect_errno) {
        install_say('Could not connect to MySQL ('.$db->connect_error.').');
        install_say('Edit DB_* in .env, create the empty database, then run php install.php again.');
        install_say('Or import '. $schema.' with phpMyAdmin.');
        return null;
    }

    $db->set_charset('utf8mb4');
    $check = $db->query("SHOW TABLES LIKE 'users'");
    if ($check && $check->num_rows > 0) {
        install_say('Database already has tables. Left them unchanged.');
        $db->close();
        return true;
    }

    $sql = file_get_contents($schema);
    if ($sql === false || trim($sql) === '') {
        install_fail('data-schema.sql is empty.');
        $db->close();
        return false;
    }

    install_say('Importing database tables...');
    if (! $db->multi_query($sql)) {
        install_fail('Database import failed: '.$db->error);
        $db->close();
        return false;
    }

    do {
        if ($result = $db->store_result()) {
            $result->free();
        }
        if ($db->errno) {
            install_fail('Database import failed: '.$db->error);
            $db->close();
            return false;
        }
    } while ($db->more_results() && $db->next_result());

    if ($db->errno) {
        install_fail('Database import failed: '.$db->error);
        $db->close();
        return false;
    }

    $db->close();
    install_say('Database tables imported.');
    return true;
}

function install_read_env(string $path): array
{
    $vars = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return $vars;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (! str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $value = trim($value);
        if (strlen($value) >= 2) {
            $quote = $value[0];
            if (($quote === '"' || $quote === "'") && str_ends_with($value, $quote)) {
                $value = substr($value, 1, -1);
            }
        }
        $vars[trim($name)] = $value;
    }

    return $vars;
}
