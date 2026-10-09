<?php

/**
 * Per-server RSA key pair for browser form encryption.
 *
 * The pair in tech-config.php is a shared placeholder. This file writes a
 * unique pair to storage/app/form-encryption-keys.php, which is gitignored.
 * Nothing here prints or returns a key into the repository.
 */

if (! function_exists('whatsjetFormEncryptionKeysPath')) {
    function whatsjetFormEncryptionKeysPath(): string
    {
        if (function_exists('storage_path')) {
            return storage_path('app/form-encryption-keys.php');
        }

        return dirname(__DIR__, 3).'/storage/app/form-encryption-keys.php';
    }
}

if (! function_exists('whatsjetFormEncryptionKeysFingerprint')) {
    function whatsjetFormEncryptionKeysFingerprint(array $keys): string
    {
        $publicKey = (string) ($keys['public_key'] ?? '');

        return substr(hash('sha256', $publicKey), 0, 16);
    }
}

if (! function_exists('whatsjetEnsureFormEncryptionKeys')) {
    /**
     * Load the server's form-encryption keys, creating them when missing.
     *
     * @param  bool  $force  Replace an existing pair. In-progress forms will fail until reload.
     * @return array{passphrase: string, public_key: string, private_key: string}
     */
    function whatsjetEnsureFormEncryptionKeys(bool $force = false): array
    {
        $path = whatsjetFormEncryptionKeysPath();

        if (! $force && is_file($path)) {
            $existing = include $path;
            if (whatsjetFormEncryptionKeysAreComplete($existing)) {
                return $existing;
            }
        }

        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('Cannot create '.$directory);
        }

        $lockPath = $path.'.lock';
        $lock = fopen($lockPath, 'c');
        if ($lock === false) {
            throw new RuntimeException('Cannot lock '.$lockPath);
        }

        try {
            if (! flock($lock, LOCK_EX)) {
                throw new RuntimeException('Cannot lock '.$lockPath);
            }

            if (! $force && is_file($path)) {
                $existing = include $path;
                if (whatsjetFormEncryptionKeysAreComplete($existing)) {
                    return $existing;
                }
            }

            $generated = whatsjetGenerateFormEncryptionKeys();
            $export = "<?php\n\nreturn ".var_export($generated, true).";\n";
            $temporary = $path.'.'.bin2hex(random_bytes(4)).'.tmp';

            if (file_put_contents($temporary, $export, LOCK_EX) === false) {
                throw new RuntimeException('Cannot write '.$temporary);
            }

            @chmod($temporary, 0600);

            if (! rename($temporary, $path)) {
                @unlink($temporary);
                throw new RuntimeException('Cannot move the new form encryption keys into place.');
            }

            @chmod($path, 0600);

            return $generated;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}

if (! function_exists('whatsjetFormEncryptionKeysAreComplete')) {
    function whatsjetFormEncryptionKeysAreComplete($keys): bool
    {
        return is_array($keys)
            && ! empty($keys['passphrase'])
            && ! empty($keys['public_key'])
            && ! empty($keys['private_key'])
            && str_contains((string) $keys['public_key'], 'BEGIN PUBLIC KEY')
            && str_contains((string) $keys['private_key'], 'PRIVATE KEY');
    }
}

if (! function_exists('whatsjetGenerateFormEncryptionKeys')) {
    /**
     * @return array{passphrase: string, public_key: string, private_key: string}
     */
    function whatsjetGenerateFormEncryptionKeys(): array
    {
        if (! function_exists('openssl_pkey_new')) {
            throw new RuntimeException('The PHP openssl extension is required to create a unique form encryption key.');
        }

        $config = [
            'private_key_bits' => 1024,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        foreach ([
            getenv('OPENSSL_CONF') ?: '',
            '/etc/ssl/openssl.cnf',
            '/usr/lib/ssl/openssl.cnf',
            '/usr/local/ssl/openssl.cnf',
            'C:/xampp/apache/conf/openssl.cnf',
            'C:/xampp/php/extras/ssl/openssl.cnf',
        ] as $opensslConfig) {
            if ($opensslConfig && is_file($opensslConfig)) {
                $config['config'] = $opensslConfig;
                break;
            }
        }

        $key = openssl_pkey_new($config);
        if ($key === false) {
            throw new RuntimeException('OpenSSL could not create a key. '.trim(openssl_error_string() ?: ''));
        }

        $passphrase = bin2hex(random_bytes(16));
        $privateKey = '';
        $exported = openssl_pkey_export($key, $privateKey, $passphrase, $config);
        if (! $exported || $privateKey === '') {
            throw new RuntimeException('OpenSSL could not export the private key. '.trim(openssl_error_string() ?: ''));
        }

        $details = openssl_pkey_get_details($key);
        $publicKey = is_array($details) ? (string) ($details['key'] ?? '') : '';
        if (! str_contains($publicKey, 'BEGIN PUBLIC KEY')) {
            throw new RuntimeException('OpenSSL could not export the public key.');
        }

        return [
            'passphrase' => $passphrase,
            'public_key' => $publicKey,
            'private_key' => $privateKey,
        ];
    }
}
