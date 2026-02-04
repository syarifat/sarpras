<?php

/**
 * User - Pengaduan List
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Pengaduan Saya');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['user']);

$userId = getCurrentUser()['id'];

$pengaduan = fetchAll("
    SELECT pg.*,
           (SELECT COUNT(*) FROM catatan_pengaduan WHERE pengaduan_id = pg.id) as jumlah_catatan
    FROM pengaduan pg 
    WHERE pg.user_id = ?
    ORDER BY 
        CASE pg.status WHEN 'pending' THEN 1 WHEN 'proses' THEN 2 ELSE 3 END,
        pg.created_at DESC
", [$userId]);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Pengaduan Saya</h1>
            <p class="text-gray-600">Daftar laporan kerusakan sarpras</p>
        </div>
        <a href="create.php" class="inline-flex items-center px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition">
            <i class="fas fa-plus mr-2"></i>Lapor Kerusakan
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="divide-y">
            <?php if (empty($pengaduan)): ?>
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-3 block"></i>
                    Belum ada pengaduan. <a href="create.php" class="text-red-600 hover:underline">Laporkan kerusakan</a>
                </div>
            <?php else: ?>
                <?php foreach ($pengaduan as $pg): ?>
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-semibold text-gray-800"><?= e($pg['judul']) ?></h3>
                                    <?= getStatusBadge($pg['status']) ?>
                                </div>
                                <p class="text-sm text-gray-600 mb-2"><?= e(substr($pg['deskripsi'], 0, 120)) ?>...</p>
                                <div class="flex items-center gap-4 text-xs text-gray-500">
                                    <span><i class="fas fa-map-marker-alt mr-1"></i><?= e($pg['lokasi'] ?? 'Tidak dicantumkan') ?></span>
                                    <span><i class="fas fa-calendar mr-1"></i><?= formatDate($pg['created_at']) ?></span>
                                    <span><i class="fas fa-comment mr-1"></i><?= $pg['jumlah_catatan'] ?> catatan</span>
                                </div>
                            </div>
                            <a href="view.php?id=<?= $pg['id'] ?>" class="text-blue-600 hover:underline text-sm ml-4">
                                Detail <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>