<?php

/**
 * Authentication Helper Functions
 * Sarpras Management System
 */

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require user to be logged in
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ' . url('index.php'));
        exit;
    }
}

/**
 * Get current user data
 */
function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'nama_lengkap' => $_SESSION['nama_lengkap'],
        'role' => $_SESSION['role']
    ];
}

/**
 * Check if current user has specific role
 */
function hasRole($roles)
{
    if (!isLoggedIn()) {
        return false;
    }

    if (is_string($roles)) {
        $roles = [$roles];
    }

    return in_array($_SESSION['role'], $roles);
}

/**
 * Require specific role(s)
 */
function requireRole($roles)
{
    requireLogin();

    if (!hasRole($roles)) {
        header('HTTP/1.0 403 Forbidden');
        echo '<h1>403 - Access Denied</h1><p>Anda tidak memiliki akses ke halaman ini.</p>';
        echo '<a href="' . url('/') . '">Kembali ke beranda</a>';
        exit;
    }
}

/**
 * Attempt to login user
 */
function attemptLogin($username, $password)
{
    $sql = "SELECT id, username, password, nama_lengkap, role FROM users WHERE username = ?";
    $user = fetch($sql, [$username]);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role'];

        // Log activity
        logActivity('LOGIN', 'User logged in');

        return true;
    }

    return false;
}

/**
 * Logout user
 */
function logout()
{
    if (isLoggedIn()) {
        logActivity('LOGOUT', 'User logged out');
    }

    session_unset();
    session_destroy();

    header('Location: ' . url('index.php'));
    exit;
}

/**
 * Get redirect URL based on user role
 */
function getRedirectUrl()
{
    if (!isLoggedIn()) {
        return url('index.php');
    }

    switch ($_SESSION['role']) {
        case 'admin':
            return url('admin/dashboard.php');
        case 'petugas':
            return url('petugas/dashboard.php');
        case 'user':
            return url('user/dashboard.php');
        default:
            return url('index.php');
    }
}

/**
 * Log activity
 */
function logActivity($aksi, $deskripsi = '')
{
    $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    $sql = "INSERT INTO activity_log (user_id, aksi, deskripsi, ip_address) VALUES (?, ?, ?, ?)";
    query($sql, [$userId, $aksi, $deskripsi, $ip]);
}
