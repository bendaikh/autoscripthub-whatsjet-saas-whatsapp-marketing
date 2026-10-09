<?php

/**
 * Core Tech Config File - 0.1.6 - 21 FEB 2020.
 *--------------------------------------------------------------------------- */

return [

    /* Standard Reaction Codes
    ------------------------------------------------------------------------- */

    'reaction_codes' => [
        1 => 'Success',
        2 => 'Error!!',
        3 => 'Validation Error',
        4 => 'Client Side Validation',
        5 => 'Unauthorized Area',
        6 => 'Invalid Access Level',
        7 => 'Invalid Request',
        8 => 'Not Found',
        9 => 'Not Authenticated',
        10 => 'Authenticated',
        11 => 'Access Denied',
        12 => 'Email Sent',
        13 => 'Email Not Send',
        14 => 'No Changes',
        15 => 'Uploading',
        16 => 'Uploading Success',
        17 => 'Uploading Error',
        18 => 'Records Not Exist',
        19 => 'Serverside Unhandled Errors',
        20 => 'Request Token Mismatch',
        21 => 'Redirect', // Data should contains the key redirect_to
        22 => 'Restriction Imposed', // use for any restrictions like subscription restriction etc
        23 => 'Debug',
    ],

    /* Display Date Formats for Moment JS library
    ------------------------------------------------------------------------- */

    'display_date_formats' => [
        1 => 'L',       //moment().format('L');    // 05/20/2015
        2 => 'l',       //moment().format('l');    // 5/20/2015
        3 => 'LL',      //moment().format('LL');   // May 20, 2015
        4 => 'll',      //moment().format('ll');   // May 20, 2015
        5 => 'LLL',     //moment().format('LLL');  // May 20, 2015 3:35 PM
        6 => 'lll',     //moment().format('lll');  // May 20, 2015 3:35 PM
        7 => 'LLLL',    //moment().format('LLLL'); // Wednesday, May 20, 2015 3:35 PM
        8 => 'llll',     //moment().format('llll'); // Wed, May 20, 2015 3:35 PM
    ],

    /* Security configurations for encrypting/decrypting form values.
     *
     * The keys below are a shared placeholder that ships with the package.
     * Do not deploy with them. Each server must use its own key pair.
     *
     * A unique pair is written automatically to
     * storage/app/form-encryption-keys.php (not in git) on the first run,
     * and by `php install.php`. To rotate the key on a server that is
     * already installed, run:
     *
     *     php scripts/regenerate-form-keys.php
     *
     * That command replaces only the file above. It does not write a new
     * private key into this file. Regenerating logs everyone out of
     * in-progress forms; ask people to reload the page.
     *
     * Manual openssl equivalent (1024-bit, to match the browser form crypto):
     *
     *     openssl genrsa -aes256 -out rsa_1024_priv.pem 1024
     *     openssl rsa -pubout -in rsa_1024_priv.pem -out rsa_1024_pub.pem
     *
    ------------------------------------------------------------------------- */
    'form_encryption' => [

        /* Passphrse for RSA Key
        --------------------------------------------------------------------- */
        'default_rsa_passphrase' => 'vDJxOIy0yP4ce0mZCi75VzQOg29cBlbg',

        /* Default Public RSA Key
        --------------------------------------------------------------------- */

        'default_rsa_public_key' => '-----BEGIN PUBLIC KEY-----
MFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBAPJwwNa//eaQYxkNsAODohg38azVtalE
h7Lw4wxlBrbDONgYaebgscpjPRloeL0kj4aLI462lcQGVAxhyh8JijsCAwEAAQ==
-----END PUBLIC KEY-----',

        /* Default Private RSA Key
        --------------------------------------------------------------------- */

        'default_rsa_private_key' => '-----BEGIN RSA PRIVATE KEY-----
Proc-Type: 4,ENCRYPTED
DEK-Info: AES-256-CBC,30F3D06DFF01D82C12801694B3E69C48

8hgsWQm+/tDyVw3kIhwESnd6USp7S+ga2km0MuJobNXchuukvetGC2hpX0LZMjQi
YpVFEQSNe32sQp7xWIxNKIdn2a1sD+TEoZFGHVlu0pOlRdpEz0+MXmflYM3EdhG0
5+Ksd/CkIItTLisCB++pmheammVN3eXXKZh57DOsogt+jEmJjgfcS97Bk5aZ9nKk
DhFKiS/UBbL4jeVFtGECzabJ2hHqQa2Beix+W+b7QsQ8ZfcaQeD9NlBvfM3Zv/2u
QQtWVvQ/OQHwaApTNPv+CedCJLXgSLbNbwqTUuL8Ydxz3IPVTzMW9rrnMu8X3KmZ
S9vRIcBqTJ0up+brIhhaEGWqnaK7IOIVS7UhuAGrXJNnUixq8fHYlVbdNzpcaeDU
uWYaxbLYI0N7XPkGNbd9XyJhC4mqUUtdPJFrvB8zdY4=
-----END RSA PRIVATE KEY-----',
    ],
];
