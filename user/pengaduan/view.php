<?php

/**
 * User - View Pengaduan Detail
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Detail Pengaduan');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['user']);

$id = intval($_GET['id'] ?? 0);
$userId = getCurrentUser()['id'];

$pengaduan = fetch("SELECT * FROM pengaduan WHERE id = ? AND user_id = ?", [$id, $userId]);

if (!$pengaduan) {
    setFlash('error', 'Pengaduan tidak ditemukan.');
    header('Location: index.php');
    exit;
}

// Get catatan
$catatan = fetchAll("
    SELECT c.*, u.nama_lengkap, u.role
    FROM catatan_pengaduan c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.pengaduan_id = ? 
    ORDER BY c.created_at ASC
", [$id]);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Detail Pengaduan</h1>
            <p class="text-gray-600">#<?= $id ?></p>
        </div>
        <a href="index.php" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <!-- Status Banner -->
    <div class="rounded-xl p-4 
        <?php if ($pengaduan['status'] === 'pending'): ?>bg-yellow-100 border border-yellow-400
        <?php elseif ($pengaduan['status'] === 'proses'): ?>bg-blue-100 border border-blue-400
        <?php elseif ($pengaduan['status'] === 'selesai'): ?>bg-green-100 border border-green-400
        <?php else: ?>bg-gray-100 border border-gray-400<?php endif; ?>">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <?php if ($pengaduan['status'] === 'pending'): ?>
                    <i class="fas fa-clock text-2xl text-yellow-600 mr-3"></i>
                    <div>
                        <p class="font-semibold text-yellow-800">Menunggu Ditindaklanjuti</p>
                        <p class="text-sm text-yellow-700">Laporan Anda sedang menunggu diproses.</p>
                    </div>
                <?php elseif ($pengaduan['status'] === 'proses'): ?>
                    <i class="fas fa-wrench text-2xl text-blue-600 mr-3"></i>
                    <div>
                        <p class="font-semibold text-blue-800">Sedang Diproses</p>
                        <p class="text-sm text-blue-700">Tim sedang menangani masalah yang Anda laporkan.</p>
                    </div>
                <?php elseif ($pengaduan['status'] === 'selesai'): ?>
                    <i class="fas fa-check-circle text-2xl text-green-600 mr-3"></i>
                    <div>
                        <p class="font-semibold text-green-800">Selesai</p>
                        <p class="text-sm text-green-700">Masalah telah ditangani dan diselesaikan.</p>
                    </div>
                <?php else: ?>
                    <i class="fas fa-times-circle text-2xl text-gray-600 mr-3"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Ditutup</p>
                        <p class="text-sm text-gray-700">Pengaduan telah ditutup.</p>
                    </div>
                <?php endif; ?>
            </div>
            <?= getStatusBadge($pengaduan['status']) ?>
        </div>
    </div>

    <!-- Pengaduan Info -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4"><?= e($pengaduan['judul']) ?></h2>
        <p class="text-gray-700 mb-6"><?= nl2br(e($pengaduan['deskripsi'])) ?></p>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Lokasi</p>
                <p class="font-medium"><?= e($pengaduan['lokasi'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-gray-500">Jenis Sarpras</p>
                <p class="font-medium"><?= e($pengaduan['jenis_sarpras'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-gray-500">Tanggal Lapor</p>
                <p class="font-medium"><?= formatDateTime($pengaduan['created_at']) ?></p>
            </div>
        </div>

        <?php if ($pengaduan['foto']): ?>
            <div class="mt-6">
                <p class="text-gray-500 mb-2">Foto</p>
                <img src="<?= url($pengaduan['foto']) ?>" alt="Foto" class="max-w-md rounded-lg">
            </div>
        <?php endif; ?>
    </div>

    <!-- Timeline / Catatan -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Riwayat & Tindak Lanjut</h3>

        <?php if (empty($catatan)): ?>
            <p class="text-gray-500 text-center py-4">Belum ada catatan dari petugas</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($catatan as $c): ?>
                    <div class="border-l-4 <?= $c['role'] === 'user' ? 'border-blue-500' : 'border-green-500' ?> pl-4 py-2">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-medium text-gray-800">
                                <?= e($c['nama_lengkap']) ?>
                                <?php if ($c['role'] !== 'user'): ?>
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded ml-2"><?= ucfirst($c['role']) ?></span>
                                <?php endif; ?>
                            </span>
                            <span class="text-xs text-gray-500"><?= formatDateTime($c['created_at']) ?></span>
                        </div>
                        <p class="text-gray-700"><?= nl2br(e($c['catatan'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>