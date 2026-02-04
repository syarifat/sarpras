<?php

/**
 * Admin - Approve/Reject Peminjaman
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Proses Peminjaman');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin', 'petugas']);

$id = intval($_GET['id'] ?? 0);
$peminjaman = fetch("
    SELECT p.*, u.nama_lengkap, u.email, s.nama as sarpras_nama, s.kode as sarpras_kode, s.jumlah_stok
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN sarpras s ON p.sarpras_id = s.id 
    WHERE p.id = ?
", [$id]);

if (!$peminjaman) {
    setFlash('error', 'Peminjaman tidak ditemukan.');
    header('Location: index.php');
    exit;
}

if ($peminjaman['status'] !== 'pending') {
    setFlash('error', 'Peminjaman ini sudah diproses.');
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        $catatan = sanitize($_POST['catatan'] ?? '');

        if ($action === 'approve') {
            // Check stock availability
            if ($peminjaman['jumlah_stok'] < $peminjaman['jumlah']) {
                $error = 'Stok tidak mencukupi. Tersedia: ' . $peminjaman['jumlah_stok'];
            } else {
                // Update peminjaman status
                query(
                    "UPDATE peminjaman SET status = 'approved', catatan_admin = ?, approved_by = ?, approved_at = NOW() WHERE id = ?",
                    [$catatan, getCurrentUser()['id'], $id]
                );

                // Decrease stock
                query(
                    "UPDATE sarpras SET jumlah_stok = jumlah_stok - ? WHERE id = ?",
                    [$peminjaman['jumlah'], $peminjaman['sarpras_id']]
                );

                logActivity('APPROVE_PEMINJAMAN', "Approved peminjaman: {$peminjaman['kode_peminjaman']}");
                setFlash('success', 'Peminjaman berhasil disetujui.');
                header('Location: receipt.php?id=' . $id);
                exit;
            }
        } elseif ($action === 'reject') {
            if (empty($catatan)) {
                $error = 'Alasan penolakan harus diisi.';
            } else {
                query(
                    "UPDATE peminjaman SET status = 'rejected', catatan_admin = ?, approved_by = ?, approved_at = NOW() WHERE id = ?",
                    [$catatan, getCurrentUser()['id'], $id]
                );

                logActivity('REJECT_PEMINJAMAN', "Rejected peminjaman: {$peminjaman['kode_peminjaman']}");
                setFlash('success', 'Peminjaman ditolak.');
                header('Location: index.php');
                exit;
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Proses Peminjaman</h1>
            <p class="text-gray-600"><?= e($peminjaman['kode_peminjaman']) ?></p>
        </div>
        <a href="index.php" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i><?= e($error) ?>
        </div>
    <?php endif; ?>

    <!-- Peminjaman Detail -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Detail Peminjaman</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Kode Peminjaman</p>
                <p class="font-medium text-gray-800 font-mono"><?= e($peminjaman['kode_peminjaman']) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Status</p>
                <p><?= getStatusBadge($peminjaman['status']) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Peminjam</p>
                <p class="font-medium text-gray-800"><?= e($peminjaman['nama_lengkap']) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Email</p>
                <p class="font-medium text-gray-800"><?= e($peminjaman['email'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Sarpras</p>
                <p class="font-medium text-gray-800"><?= e($peminjaman['sarpras_nama']) ?></p>
                <p class="text-xs text-gray-500"><?= e($peminjaman['sarpras_kode']) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Jumlah Dipinjam</p>
                <p class="font-medium text-gray-800"><?= $peminjaman['jumlah'] ?> unit</p>
                <p class="text-xs <?= $peminjaman['jumlah_stok'] >= $peminjaman['jumlah'] ? 'text-green-600' : 'text-red-600' ?>">
                    Stok tersedia: <?= $peminjaman['jumlah_stok'] ?> unit
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Tanggal Pinjam</p>
                <p class="font-medium text-gray-800"><?= formatDate($peminjaman['tgl_pinjam'], 'l, d F Y') ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Tanggal Kembali (Rencana)</p>
                <p class="font-medium text-gray-800"><?= formatDate($peminjaman['tgl_kembali_rencana'], 'l, d F Y') ?></p>
            </div>
            <div class="col-span-2">
                <p class="text-sm text-gray-500">Tujuan Peminjaman</p>
                <p class="font-medium text-gray-800"><?= e($peminjaman['tujuan'] ?? '-') ?></p>
            </div>
        </div>
    </div>

    <!-- Action Form -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Keputusan</h2>

        <form method="POST" action="">
            <?= csrfField() ?>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan/Alasan</label>
                <textarea name="catatan" rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Isi catatan atau alasan penolakan..."><?= e($_POST['catatan'] ?? '') ?></textarea>
                <p class="text-xs text-gray-500 mt-1">Wajib diisi jika menolak peminjaman</p>
            </div>

            <div class="flex gap-4">
                <?php if ($peminjaman['jumlah_stok'] >= $peminjaman['jumlah']): ?>
                    <button type="submit" name="action" value="approve"
                        class="flex-1 py-3 px-4 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition"
                        onclick="return confirm('Setujui peminjaman ini?')">
                        <i class="fas fa-check mr-2"></i>Setujui
                    </button>
                <?php else: ?>
                    <button type="button" disabled
                        class="flex-1 py-3 px-4 bg-gray-400 text-white font-medium rounded-lg cursor-not-allowed">
                        <i class="fas fa-times mr-2"></i>Stok Tidak Cukup
                    </button>
                <?php endif; ?>

                <button type="submit" name="action" value="reject"
                    class="flex-1 py-3 px-4 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition"
                    onclick="return confirm('Tolak peminjaman ini?')">
                    <i class="fas fa-times mr-2"></i>Tolak
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>