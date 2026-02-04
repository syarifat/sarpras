<?php

/**
 * Admin - View Sarpras Detail
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Detail Sarpras');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin']);

$id = intval($_GET['id'] ?? 0);
$sarpras = fetch("
    SELECT s.*, k.nama as kategori_nama 
    FROM sarpras s 
    LEFT JOIN kategori_sarpras k ON s.kategori_id = k.id 
    WHERE s.id = ?
", [$id]);

if (!$sarpras) {
    setFlash('error', 'Sarpras tidak ditemukan.');
    header('Location: index.php');
    exit;
}

// Get borrowing history for this item
$peminjamanHistory = fetchAll("
    SELECT p.*, u.nama_lengkap 
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.sarpras_id = ? 
    ORDER BY p.created_at DESC 
    LIMIT 10
", [$id]);

// Get return history
$pengembalianHistory = fetchAll("
    SELECT pg.*, pm.kode_peminjaman, u.nama_lengkap 
    FROM pengembalian pg 
    JOIN peminjaman pm ON pg.peminjaman_id = pm.id 
    JOIN users u ON pm.user_id = u.id 
    WHERE pm.sarpras_id = ? 
    ORDER BY pg.created_at DESC 
    LIMIT 10
", [$id]);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Detail Sarpras</h1>
            <p class="text-gray-600"><?= e($sarpras['kode']) ?></p>
        </div>
        <div class="flex gap-2">
            <a href="edit.php?id=<?= $id ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <a href="index.php" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Sarpras</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Kode</p>
                    <p class="font-medium text-gray-800 font-mono"><?= e($sarpras['kode']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Nama</p>
                    <p class="font-medium text-gray-800"><?= e($sarpras['nama']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Kategori</p>
                    <p class="font-medium text-gray-800"><?= e($sarpras['kategori_nama']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Lokasi</p>
                    <p class="font-medium text-gray-800"><?= e($sarpras['lokasi'] ?? '-') ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Stok Tersedia</p>
                    <p class="font-medium text-gray-800"><?= $sarpras['jumlah_stok'] ?> unit</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Kondisi</p>
                    <p class="font-medium"><?= getConditionBadge($sarpras['kondisi']) ?></p>
                </div>
                <div class="col-span-2">
                    <p class="text-sm text-gray-500">Deskripsi</p>
                    <p class="font-medium text-gray-800"><?= e($sarpras['deskripsi'] ?? '-') ?></p>
                </div>
            </div>
        </div>

        <!-- Photo -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Foto</h2>
            <?php if ($sarpras['foto']): ?>
                <img src="/sarpras_lagi/<?= e($sarpras['foto']) ?>" alt="Foto sarpras" class="w-full rounded-lg">
            <?php else: ?>
                <div class="w-full h-48 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-image text-4xl text-gray-400"></i>
                </div>
                <p class="text-sm text-gray-500 text-center mt-2">Tidak ada foto</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Borrowing History -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-800">Riwayat Peminjaman</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peminjam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl Pinjam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl Kembali</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (empty($peminjamanHistory)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada riwayat peminjaman</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($peminjamanHistory as $p): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-mono text-sm"><?= e($p['kode_peminjaman']) ?></td>
                                <td class="px-6 py-4"><?= e($p['nama_lengkap']) ?></td>
                                <td class="px-6 py-4 text-sm"><?= formatDate($p['tgl_pinjam']) ?></td>
                                <td class="px-6 py-4 text-sm"><?= formatDate($p['tgl_kembali_rencana']) ?></td>
                                <td class="px-6 py-4"><?= getStatusBadge($p['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Return History -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-800">Riwayat Pengembalian & Kondisi</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Peminjaman</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peminjam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl Kembali</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kondisi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (empty($pengembalianHistory)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada riwayat pengembalian</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pengembalianHistory as $pg): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-mono text-sm"><?= e($pg['kode_peminjaman']) ?></td>
                                <td class="px-6 py-4"><?= e($pg['nama_lengkap']) ?></td>
                                <td class="px-6 py-4 text-sm"><?= formatDate($pg['tgl_pengembalian']) ?></td>
                                <td class="px-6 py-4"><?= getConditionBadge($pg['kondisi_alat']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600"><?= e($pg['deskripsi_kerusakan'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>