<?php

/**
 * Vercel Front Controller
 * Routes all requests to appropriate PHP files
 */

// Get the requested URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// Remove query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Default to index.php if path is root
if ($path === '/' || $path === '') {
    $path = '/index.php';
}

// Add .php extension if not present and not a static file
if (!preg_match('/\.(php|css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/', $path)) {
    // Check if it's a directory path (ends with /)
    if (substr($path, -1) === '/') {
        $path .= 'index.php';
    } else {
        $path .= '/index.php';
    }
}

// Build the full file path
$filePath = __DIR__ . '/..' . $path;

// Check if file exists
if (file_exists($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
    // Change to the directory of the file for relative includes
    chdir(dirname($filePath));

    // Include the PHP file
    require $filePath;
} else {
    // File not found
    http_response_code(404);
    echo '<!DOCTYPE html>
<html>
<head>
    <title>404 - Page Not Found</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #333; }
        p { color: #666; }
        a { color: #3b82f6; }
    </style>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>Halaman yang Anda cari tidak ditemukan.</p>
    <p>Path: ' . htmlspecialchars($path) . '</p>
    <p><a href="/">Kembali ke Beranda</a></p>
</body>
</html>';
}
