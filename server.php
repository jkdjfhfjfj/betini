<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
$assetRoot = realpath(__DIR__ . '/assets');
$requestedPath = realpath(__DIR__ . '/' . ltrim($uri, '/'));
$assetExtension = strtolower(pathinfo($requestedPath ?: '', PATHINFO_EXTENSION));
if ($uri !== '/' && $assetRoot && $requestedPath && is_file($requestedPath)
    && strpos($requestedPath, $assetRoot . DIRECTORY_SEPARATOR) === 0
    && in_array($assetExtension, ['css', 'js', 'map', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico', 'woff', 'woff2', 'ttf', 'eot'], true)) {
    return false;
}

require_once __DIR__ . '/index.php';
