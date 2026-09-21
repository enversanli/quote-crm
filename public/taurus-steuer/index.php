<?php
// Serves the static site stored in /temp as if it were the site root.
// Works together with .htaccess, which routes any request that isn't
// a real file/folder into this script.
$root = __DIR__ . '/temp';

$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri  = urldecode($uri);
$path = realpath($root . $uri);

if ($path !== false && is_dir($path)) {
    $path = rtrim($path, '/') . '/index.html';
}
if ($uri === '/' || $uri === '') {
    $path = $root . '/index.html';
}

$realRoot = realpath($root);
$realPath = $path !== false ? realpath($path) : false;

// Block path traversal and serve 404 for missing files
if ($realRoot === false || $realPath === false || strpos($realPath, $realRoot) !== 0 || !is_file($realPath)) {
    http_response_code(404);
    $fallback = $root . '/index.html';
    if (is_file($fallback)) {
        readfile($fallback); // no dedicated 404 page yet — falls back to the homepage
    }
    exit;
}

$mime = [
    'html' => 'text/html; charset=UTF-8',
    'css'  => 'text/css; charset=UTF-8',
    'js'   => 'application/javascript; charset=UTF-8',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'svg'  => 'image/svg+xml',
    'ico'  => 'image/x-icon',
    'webp' => 'image/webp',
];
$ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
header('Content-Type: ' . ($mime[$ext] ?? 'application/octet-stream'));
header('Cache-Control: public, max-age=3600');
readfile($realPath);
