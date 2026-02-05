<?php

/**
 * Admin - View Peminjaman Detail
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Detail Peminjaman');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin', 'petugas']);

$id = intval($_GET['id'] ?? 0);
$peminjaman = fetch("
    SELECT p.*, u.nama_lengkap, u.email, u.phone,
           s.nama as sarpras_nama, s.kode as sarpras_kode, s.lokasi as sarpras_lokasi,
           a.nama_lengkap as approved_by_name
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN sarpras s ON p.sarpras_id = s.id 
    LEFT JOIN users a ON p.approved_by = a.id
    WHERE p.id = ?
", [$id]);

if (!$peminjaman) {
    setFlash('error', 'Peminjaman tidak ditemukan.');
    header('Location: index.php');
    exit;
}

// Get return data if exists
$pengembalian = fetch("SELECT * FROM pengembalian WHERE peminjaman_id = ?", [$id]);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Detail Peminjaman</h1>
            <p class="text-gray-600"><?= e($peminjaman['kode_peminjaman']) ?></p>
        </div>
        <div class="flex gap-2">
            <?php if ($peminjaman['status'] === 'pending'): ?>
                <a href="approve.php?id=<?= $id ?>" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-check mr-2"></i>Proses
                </a>
            <?php endif; ?>
            <?php if ($peminjaman['status'] === 'approved'): ?>
                <a href="inspection.php?id=<?= $id ?>" class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition">
                    <i class="fas fa-clipboard-check mr-2"></i>Serah Terima
                </a>
            <?php endif; ?>
            <?php if (in_array($peminjaman['status'], ['approved', 'active'])): ?>
                <a href="receipt.php?id=<?= $id ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-print mr-2"></i>Cetak Bukti
                </a>
            <?php endif; ?>
            <a href="index.php" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Peminjaman Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Peminjaman</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">Kode</span>
                    <span class="font-mono font-medium"><?= e($peminjaman['kode_peminjaman']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Status</span>
                    <?= getStatusBadge($peminjaman['status']) ?>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Tgl Pinjam</span>
                    <span><?= formatDate($peminjaman['tgl_pinjam'], 'd M Y') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Tgl Kembali (Rencana)</span>
                    <span><?= formatDate($peminjaman['tgl_kembali_rencana'], 'd M Y') ?></span>
                </div>
                <?php if ($peminjaman['tgl_kembali_aktual']): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tgl Kembali (Aktual)</span>
                        <span><?= formatDate($peminjaman['tgl_kembali_aktual'], 'd M Y') ?></span>
                    </div>
                <?php endif; ?>
                <div class="flex justify-between">
                    <span class="text-gray-500">Diajukan</span>
                    <span><?= formatDateTime($peminjaman['created_at']) ?></span>
                </div>
                <?php if ($peminjaman['approved_at']): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Diproses</span>
                        <span><?= formatDateTime($peminjaman['approved_at']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Diproses Oleh</span>
                        <span><?= e($peminjaman['approved_by_name'] ?? '-') ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Peminjam Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Peminjam</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">Nama</span>
                    <span class="font-medium"><?= e($peminjaman['nama_lengkap']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Email</span>
                    <span><?= e($peminjaman['email'] ?? '-') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Telepon</span>
                    <span><?= e($peminjaman['phone'] ?? '-') ?></span>
                </div>
            </div>
        </div>

        <!-- Sarpras Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Sarpras</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">Kode</span>
                    <span class="font-mono"><?= e($peminjaman['sarpras_kode']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Nama</span>
                    <span class="font-medium"><?= e($peminjaman['sarpras_nama']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Lokasi</span>
                    <span><?= e($peminjaman['sarpras_lokasi'] ?? '-') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Jumlah Dipinjam</span>
                    <span class="font-medium"><?= $peminjaman['jumlah'] ?> unit</span>
                </div>
            </div>
        </div>

        <!-- Tujuan -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Tujuan Peminjaman</h2>
            <p class="text-gray-700"><?= e($peminjaman['tujuan'] ?? 'Tidak ada keterangan') ?></p>

            <?php if ($peminjaman['catatan_admin']): ?>
                <h3 class="text-md font-semibold text-gray-800 mt-4 mb-2">Catatan Admin</h3>
                <p class="text-gray-700"><?= e($peminjaman['catatan_admin']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Return Info if exists -->
    <?php if ($pengembalian): ?>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pengembalian</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Tanggal Pengembalian</p>
                    <p class="font-medium"><?= formatDate($pengembalian['tgl_pengembalian'], 'd M Y') ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Kondisi Alat</p>
                    <?= getConditionBadge($pengembalian['kondisi_alat']) ?>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Deskripsi</p>
                    <p class="font-medium"><?= e($pengembalian['deskripsi_kerusakan'] ?? '-') ?></p>
                </div>
            </div>
            <?php if ($pengembalian['foto_pengembalian']): ?>
                <div class="mt-4">
                    <p class="text-sm text-gray-500 mb-2">Foto Pengembalian</p>
                    <img src="<?= url($pengembalian['foto_pengembalian']) ?>" class="w-48 h-48 object-cover rounded-lg">
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>