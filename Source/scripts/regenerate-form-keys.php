<?php

/**
 * Replace this server's form-encryption RSA key pair.
 *
 * Usage, from the Source folder:
 *     php scripts/regenerate-form-keys.php
 *
 * Writes storage/app/form-encryption-keys.php only. That file is not part
 * of the download and must not be committed. The placeholder key in
 * tech-config.php is left unchanged.
 */

require __DIR__.'/../app/Yantrana/Support/form-encryption-keys.php';

try {
    $keys = whatsjetEnsureFormEncryptionKeys(true);
} catch (Throwable $exception) {
    fwrite(STDERR, "Could not create a new form encryption key.\n".$exception->getMessage()."\n");
    exit(1);
}

$fingerprint = whatsjetFormEncryptionKeysFingerprint($keys);
$path = whatsjetFormEncryptionKeysPath();

echo "New form encryption key written.\n";
echo "File: {$path}\n";
echo "Public key fingerprint: {$fingerprint}\n";
echo "This file stays on this server. Do not commit it or copy it into tech-config.php.\n";
echo "Ask anyone with a form open to reload the page.\n";
