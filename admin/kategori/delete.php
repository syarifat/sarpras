<?php

/**
 * Admin - Delete Kategori
 * Sarpras Management System
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin']);

$id = intval($_GET['id'] ?? 0);
$kategori = fetch("SELECT * FROM kategori_sarpras WHERE id = ?", [$id]);

if (!$kategori) {
    setFlash('error', 'Kategori tidak ditemukan.');
    header('Location: index.php');
    exit;
}

// Check for related sarpras
$hasSarpras = fetch("SELECT COUNT(*) as count FROM sarpras WHERE kategori_id = ?", [$id])['count'] > 0;

if ($hasSarpras) {
    setFlash('error', 'Kategori tidak dapat dihapus karena masih memiliki data sarpras.');
    header('Location: index.php');
    exit;
}

query("DELETE FROM kategori_sarpras WHERE id = ?", [$id]);
logActivity('DELETE_KATEGORI', "Deleted category: {$kategori['nama']}");
setFlash('success', 'Kategori berhasil dihapus.');

header('Location: index.php');
exit;
