<?php
declare(strict_types=1);

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

date_default_timezone_set('Asia/Kolkata');

const APP_NAME = 'QuickBite';
const CURRENCY = 'Rs.';

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if (basename($scriptDir) === 'admin') {
    $scriptDir = dirname($scriptDir);
}
$scriptDir = rtrim($scriptDir, '/');
define('APP_BASE', $scriptDir === '/' ? '' : $scriptDir);

