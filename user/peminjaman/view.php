<?php

/**
 * User - View Peminjaman Detail
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Detail Peminjaman');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['user']);

$id = intval($_GET['id'] ?? 0);
$userId = getCurrentUser()['id'];

$peminjaman = fetch("
    SELECT p.*, s.nama as sarpras_nama, s.kode as sarpras_kode, s.foto as sarpras_foto,
           k.nama as kategori_nama, a.nama_lengkap as approved_by_name
    FROM peminjaman p 
    JOIN sarpras s ON p.sarpras_id = s.id 
    LEFT JOIN kategori_sarpras k ON s.kategori_id = k.id
    LEFT JOIN users a ON p.approved_by = a.id
    WHERE p.id = ? AND p.user_id = ?
", [$id, $userId]);

if (!$peminjaman) {
    setFlash('error', 'Peminjaman tidak ditemukan.');
    header('Location: index.php');
    exit;
}

// Get return info if exists
$pengembalian = fetch("SELECT * FROM pengembalian WHERE peminjaman_id = ?", [$id]);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Detail Peminjaman</h1>
            <p class="text-gray-600"><?= e($peminjaman['kode_peminjaman']) ?></p>
        </div>
        <div class="flex gap-2">
            <?php if (in_array($peminjaman['status'], ['approved', 'active'])): ?>
                <a href="receipt.php?id=<?= $id ?>" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-print mr-2"></i>Cetak Bukti
                </a>
            <?php endif; ?>
            <a href="index.php" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Status Banner -->
    <div class="rounded-xl p-4 
        <?php if ($peminjaman['status'] === 'pending'): ?>bg-yellow-100 border border-yellow-400
        <?php elseif ($peminjaman['status'] === 'approved' || $peminjaman['status'] === 'active'): ?>bg-green-100 border border-green-400
        <?php elseif ($peminjaman['status'] === 'rejected'): ?>bg-red-100 border border-red-400
        <?php else: ?>bg-gray-100 border border-gray-400<?php endif; ?>">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <?php if ($peminjaman['status'] === 'pending'): ?>
                    <i class="fas fa-clock text-2xl text-yellow-600 mr-3"></i>
                    <div>
                        <p class="font-semibold text-yellow-800">Menunggu Persetujuan</p>
                        <p class="text-sm text-yellow-700">Permintaan Anda sedang diproses oleh admin.</p>
                    </div>
                <?php elseif ($peminjaman['status'] === 'approved' || $peminjaman['status'] === 'active'): ?>
                    <i class="fas fa-check-circle text-2xl text-green-600 mr-3"></i>
                    <div>
                        <p class="font-semibold text-green-800">Disetujui</p>
                        <p class="text-sm text-green-700">Silakan ambil barang dan tunjukkan bukti peminjaman.</p>
                    </div>
                <?php elseif ($peminjaman['status'] === 'rejected'): ?>
                    <i class="fas fa-times-circle text-2xl text-red-600 mr-3"></i>
                    <div>
                        <p class="font-semibold text-red-800">Ditolak</p>
                        <p class="text-sm text-red-700">Alasan: <?= e($peminjaman['catatan_admin'] ?? 'Tidak ada keterangan') ?></p>
                    </div>
                <?php else: ?>
                    <i class="fas fa-undo text-2xl text-gray-600 mr-3"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Dikembalikan</p>
                        <p class="text-sm text-gray-700">Peminjaman telah selesai.</p>
                    </div>
                <?php endif; ?>
            </div>
            <?= getStatusBadge($peminjaman['status']) ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Peminjaman Info -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-4">Informasi Peminjaman</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Kode Peminjaman</p>
                    <p class="font-mono font-medium"><?= e($peminjaman['kode_peminjaman']) ?></p>
                </div>
                <div>
                    <p class="text-gray-500">Tanggal Pengajuan</p>
                    <p class="font-medium"><?= formatDateTime($peminjaman['created_at']) ?></p>
                </div>
                <div>
                    <p class="text-gray-500">Sarpras</p>
                    <p class="font-medium"><?= e($peminjaman['sarpras_nama']) ?></p>
                    <p class="text-xs text-gray-500"><?= e($peminjaman['sarpras_kode']) ?> | <?= e($peminjaman['kategori_nama']) ?></p>
                </div>
                <div>
                    <p class="text-gray-500">Jumlah</p>
                    <p class="font-medium"><?= $peminjaman['jumlah'] ?> unit</p>
                </div>
                <div>
                    <p class="text-gray-500">Tanggal Pinjam</p>
                    <p class="font-medium"><?= formatDate($peminjaman['tgl_pinjam'], 'l, d F Y') ?></p>
                </div>
                <div>
                    <p class="text-gray-500">Tanggal Kembali</p>
                    <p class="font-medium"><?= formatDate($peminjaman['tgl_kembali_rencana'], 'l, d F Y') ?></p>
                </div>
                <div class="col-span-2">
                    <p class="text-gray-500">Tujuan</p>
                    <p class="font-medium"><?= e($peminjaman['tujuan']) ?></p>
                </div>
                <?php if ($peminjaman['catatan_admin'] && $peminjaman['status'] !== 'rejected'): ?>
                    <div class="col-span-2">
                        <p class="text-gray-500">Catatan Admin</p>
                        <p class="font-medium"><?= e($peminjaman['catatan_admin']) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sarpras Photo -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-4">Foto Sarpras</h2>
            <?php if ($peminjaman['sarpras_foto']): ?>
                <img src="/sarpras_lagi/<?= e($peminjaman['sarpras_foto']) ?>" alt="Foto" class="w-full rounded-lg">
            <?php else: ?>
                <div class="w-full h-48 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-image text-4xl text-gray-400"></i>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Return Info if exists -->
    <?php if ($pengembalian): ?>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-4">Informasi Pengembalian</h2>
            <div class="grid grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Tanggal Pengembalian</p>
                    <p class="font-medium"><?= formatDate($pengembalian['tgl_pengembalian'], 'd F Y') ?></p>
                </div>
                <div>
                    <p class="text-gray-500">Kondisi Alat</p>
                    <?= getConditionBadge($pengembalian['kondisi_alat']) ?>
                </div>
                <?php if ($pengembalian['deskripsi_kerusakan']): ?>
                    <div>
                        <p class="text-gray-500">Deskripsi</p>
                        <p class="font-medium"><?= e($pengembalian['deskripsi_kerusakan']) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>