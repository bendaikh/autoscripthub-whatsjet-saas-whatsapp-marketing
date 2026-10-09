<?php

putenv('LC_ALL=en');
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Dependencies are not in the download. install.php creates them.
if (! is_file(__DIR__.'/../vendor/autoload.php')) {
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>WhatsJet setup</title></head><body style="font-family:sans-serif;max-width:40rem;margin:3rem auto;line-height:1.5">';
    echo '<h1>WhatsJet is not installed yet</h1>';
    echo '<p>The <code>vendor</code> folder is missing. On the server, open the <code>Source</code> folder and run:</p>';
    echo '<pre>php install.php</pre>';
    echo '<p>Full steps, requirements, and common errors are in <code>INSTALL.md</code> at the top of the package.</p>';
    echo '</body></html>';
    exit(1);
}

if (! is_file(__DIR__.'/../.env')) {
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>WhatsJet setup</title></head><body style="font-family:sans-serif;max-width:40rem;margin:3rem auto;line-height:1.5">';
    echo '<h1>Configuration file missing</h1>';
    echo '<p>Copy <code>Source/.env.example</code> to <code>Source/.env</code>, set your website address and database login, then run <code>php install.php</code> from the <code>Source</code> folder.</p>';
    echo '</body></html>';
    exit(1);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
