<?php

/**
 * Admin - Delete Sarpras
 * Sarpras Management System
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin']);

$id = intval($_GET['id'] ?? 0);
$sarpras = fetch("SELECT * FROM sarpras WHERE id = ?", [$id]);

if (!$sarpras) {
    setFlash('error', 'Sarpras tidak ditemukan.');
    header('Location: index.php');
    exit;
}

// Check for related borrowings
$hasPeminjaman = fetch("SELECT COUNT(*) as count FROM peminjaman WHERE sarpras_id = ?", [$id])['count'] > 0;

if ($hasPeminjaman) {
    setFlash('error', 'Sarpras tidak dapat dihapus karena memiliki data peminjaman.');
    header('Location: index.php');
    exit;
}

// Delete photo if exists
if ($sarpras['foto']) {
    deleteFile($sarpras['foto']);
}

query("DELETE FROM sarpras WHERE id = ?", [$id]);
logActivity('DELETE_SARPRAS', "Deleted sarpras: {$sarpras['kode']} - {$sarpras['nama']}");
setFlash('success', 'Sarpras berhasil dihapus.');

header('Location: index.php');
exit;
