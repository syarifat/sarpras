<?php

/**
 * Admin - Delete User
 * Sarpras Management System
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin']);

$id = intval($_GET['id'] ?? 0);
$user = fetch("SELECT * FROM users WHERE id = ?", [$id]);

if (!$user) {
    setFlash('error', 'Pengguna tidak ditemukan.');
    header('Location: index.php');
    exit;
}

// Prevent self-delete
if ($id == getCurrentUser()['id']) {
    setFlash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
    header('Location: index.php');
    exit;
}

// Check for related data
$hasPeminjaman = fetch("SELECT COUNT(*) as count FROM peminjaman WHERE user_id = ?", [$id])['count'] > 0;
$hasPengaduan = fetch("SELECT COUNT(*) as count FROM pengaduan WHERE user_id = ?", [$id])['count'] > 0;

if ($hasPeminjaman || $hasPengaduan) {
    setFlash('error', 'Pengguna tidak dapat dihapus karena memiliki data peminjaman atau pengaduan.');
    header('Location: index.php');
    exit;
}

// Delete user
query("DELETE FROM users WHERE id = ?", [$id]);
logActivity('DELETE_USER', "Deleted user: {$user['username']}");
setFlash('success', 'Pengguna berhasil dihapus.');

header('Location: index.php');
exit;
