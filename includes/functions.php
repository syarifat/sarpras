<?php

/**
 * Common Utility Functions
 * Sarpras Management System
 */

// Auto-detect base URL based on environment
// For Vercel deployment, use root path
// For local development (Laragon), use /sarpras_lagi/
if (!defined('BASE_URL')) {
    $isVercel = isset($_SERVER['VERCEL']) || strpos($_SERVER['HTTP_HOST'] ?? '', 'vercel.app') !== false || strpos($_SERVER['HTTP_HOST'] ?? '', 'sat-project.me') !== false;
    define('BASE_URL', $isVercel ? '' : '/sarpras_lagi');
}

/**
 * Generate URL with base path
 */
function url($path = '')
{
    $path = ltrim($path, '/');
    return BASE_URL . '/' . $path;
}

/**
 * Sanitize input string
 */
function sanitize($input)
{
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Escape for HTML output
 */
function e($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format date to Indonesian format
 */
function formatDate($date, $format = 'd M Y')
{
    if (empty($date)) return '-';
    return date($format, strtotime($date));
}

/**
 * Format datetime to Indonesian format
 */
function formatDateTime($datetime, $format = 'd M Y H:i')
{
    if (empty($datetime)) return '-';
    return date($format, strtotime($datetime));
}

/**
 * Generate unique code
 */
function generateCode($prefix = 'PJM', $length = 3)
{
    $year = date('Y');
    $random = str_pad(mt_rand(1, 999), $length, '0', STR_PAD_LEFT);
    return $prefix . '-' . $year . '-' . $random . '-' . substr(uniqid(), -4);
}

/**
 * Generate unique code (alias for generateCode with Indonesian naming)
 */
function generateKode($prefix = 'PJM', $length = 3)
{
    return generateCode($prefix, $length);
}

/**
 * Upload file
 */
function uploadFile($file, $destination = 'assets/uploads/', $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'])
{
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return null;
    }

    $fileName = $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Validate extension
    if (!in_array($fileExt, $allowedTypes)) {
        return ['error' => 'Tipe file tidak diizinkan. Gunakan: ' . implode(', ', $allowedTypes)];
    }

    // Validate size (max 5MB)
    if ($fileSize > 5 * 1024 * 1024) {
        return ['error' => 'Ukuran file terlalu besar. Maksimal 5MB.'];
    }

    // Create unique filename
    $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
    $uploadPath = __DIR__ . '/../' . $destination . $newFileName;

    // Create directory if not exists
    $dir = dirname($uploadPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    // Move file
    if (move_uploaded_file($fileTmp, $uploadPath)) {
        return ['success' => true, 'filename' => $newFileName, 'path' => $destination . $newFileName];
    }

    return ['error' => 'Gagal mengupload file.'];
}

/**
 * Delete file
 */
function deleteFile($path)
{
    $fullPath = __DIR__ . '/../' . $path;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Get status badge HTML
 */
function getStatusBadge($status)
{
    $badges = [
        'pending' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>',
        'approved' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Disetujui</span>',
        'rejected' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>',
        'active' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>',
        'returned' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Dikembalikan</span>',
        'overdue' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Terlambat</span>',
        'proses' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Diproses</span>',
        'selesai' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>',
        'ditutup' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Ditutup</span>',
    ];

    return $badges[$status] ?? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">' . ucfirst($status) . '</span>';
}

/**
 * Get condition badge HTML
 */
function getConditionBadge($kondisi)
{
    $badges = [
        'baik' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Baik</span>',
        'rusak_ringan' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Rusak Ringan</span>',
        'rusak_berat' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rusak Berat</span>',
        'hilang' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-800 text-white">Hilang</span>',
    ];

    return $badges[$kondisi] ?? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">' . ucfirst($kondisi) . '</span>';
}

/**
 * Set flash message
 */
function setFlash($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message
 */
function displayFlash()
{
    $flash = getFlash();
    if (!$flash) return '';

    $colors = [
        'success' => 'bg-green-100 border-green-400 text-green-700',
        'error' => 'bg-red-100 border-red-400 text-red-700',
        'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
        'info' => 'bg-blue-100 border-blue-400 text-blue-700',
    ];

    $color = $colors[$flash['type']] ?? $colors['info'];

    return '<div class="' . $color . ' px-4 py-3 rounded relative mb-4 border" role="alert">
        <span class="block sm:inline">' . e($flash['message']) . '</span>
    </div>';
}

/**
 * Pagination helper
 */
function paginate($totalItems, $currentPage = 1, $perPage = 10)
{
    $totalPages = ceil($totalItems / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'per_page' => $perPage,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
    ];
}

/**
 * CSRF Token generation
 */
function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF Token validation
 */
function validateCsrfToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * CSRF Token input field
 */
function csrfField()
{
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}
