<?php

/**
 * Petugas - Dashboard
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Dashboard Petugas');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['petugas']);

// Get statistics
$stats = [
    'pending_peminjaman' => fetch("SELECT COUNT(*) as count FROM peminjaman WHERE status = 'pending'")['count'],
    'active_peminjaman' => fetch("SELECT COUNT(*) as count FROM peminjaman WHERE status IN ('approved', 'active')")['count'],
    'pending_pengaduan' => fetch("SELECT COUNT(*) as count FROM pengaduan WHERE status = 'pending'")['count'],
    'overdue_peminjaman' => fetch("SELECT COUNT(*) as count FROM peminjaman WHERE status IN ('approved', 'active') AND tgl_kembali_rencana < CURDATE()")['count'],
];

// Get pending peminjaman
$pendingPeminjaman = fetchAll("
    SELECT p.*, u.nama_lengkap, s.nama as sarpras_nama 
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN sarpras s ON p.sarpras_id = s.id 
    WHERE p.status = 'pending'
    ORDER BY p.created_at ASC
    LIMIT 10
");

// Get overdue
$overduePeminjaman = fetchAll("
    SELECT p.*, u.nama_lengkap, s.nama as sarpras_nama 
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN sarpras s ON p.sarpras_id = s.id 
    WHERE p.status IN ('approved', 'active') AND p.tgl_kembali_rencana < CURDATE()
    ORDER BY p.tgl_kembali_rencana ASC
    LIMIT 10
");

// Get pending pengaduan
$pendingPengaduan = fetchAll("
    SELECT pg.*, u.nama_lengkap 
    FROM pengaduan pg 
    JOIN users u ON pg.user_id = u.id 
    WHERE pg.status IN ('pending', 'proses')
    ORDER BY pg.created_at ASC
    LIMIT 10
");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="space-y-6">
    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-r from-yellow-500 to-amber-600 rounded-xl shadow-sm p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Peminjaman Pending</p>
                    <p class="text-3xl font-bold"><?= $stats['pending_peminjaman'] ?></p>
                </div>
                <i class="fas fa-clock text-4xl opacity-50"></i>
            </div>
            <a href="../admin/peminjaman/index.php?status=pending" class="text-xs opacity-90 hover:opacity-100 mt-2 inline-block">
                Lihat semua <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow-sm p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Peminjaman Aktif</p>
                    <p class="text-3xl font-bold"><?= $stats['active_peminjaman'] ?></p>
                </div>
                <i class="fas fa-hand-holding text-4xl opacity-50"></i>
            </div>
        </div>
        <div class="bg-gradient-to-r from-red-500 to-rose-600 rounded-xl shadow-sm p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Terlambat</p>
                    <p class="text-3xl font-bold"><?= $stats['overdue_peminjaman'] ?></p>
                </div>
                <i class="fas fa-exclamation-triangle text-4xl opacity-50"></i>
            </div>
        </div>
        <div class="bg-gradient-to-r from-purple-500 to-violet-600 rounded-xl shadow-sm p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Pengaduan Aktif</p>
                    <p class="text-3xl font-bold"><?= $stats['pending_pengaduan'] ?></p>
                </div>
                <i class="fas fa-bullhorn text-4xl opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="../admin/peminjaman/" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-lg transition flex items-center">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-4">
                <i class="fas fa-clipboard-list text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-800">Kelola Peminjaman</h3>
                <p class="text-sm text-gray-500">Proses dan pantau peminjaman</p>
            </div>
        </a>
        <a href="../admin/pengembalian/" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-lg transition flex items-center">
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center mr-4">
                <i class="fas fa-undo text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-800">Proses Pengembalian</h3>
                <p class="text-sm text-gray-500">Terima dan catat kondisi</p>
            </div>
        </a>
        <a href="../admin/pengaduan/" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-lg transition flex items-center">
            <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mr-4">
                <i class="fas fa-bullhorn text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-800">Kelola Pengaduan</h3>
                <p class="text-sm text-gray-500">Tangani laporan kerusakan</p>
            </div>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Pending Peminjaman -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-clock text-yellow-500 mr-2"></i>Peminjaman Menunggu
                </h2>
                <a href="../admin/peminjaman/?status=pending" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
            </div>
            <div class="divide-y">
                <?php if (empty($pendingPeminjaman)): ?>
                    <p class="p-4 text-gray-500 text-center">Tidak ada peminjaman pending</p>
                <?php else: ?>
                    <?php foreach ($pendingPeminjaman as $p): ?>
                        <div class="p-4 flex items-center justify-between hover:bg-gray-50">
                            <div>
                                <p class="font-medium text-gray-800"><?= e($p['nama_lengkap']) ?></p>
                                <p class="text-sm text-gray-500"><?= e($p['sarpras_nama']) ?></p>
                            </div>
                            <a href="../admin/peminjaman/approve.php?id=<?= $p['id'] ?>"
                                class="px-3 py-1 bg-yellow-500 text-white text-sm rounded-lg hover:bg-yellow-600">
                                Proses
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Overdue -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>Peminjaman Terlambat
                </h2>
            </div>
            <div class="divide-y">
                <?php if (empty($overduePeminjaman)): ?>
                    <p class="p-4 text-gray-500 text-center">Tidak ada peminjaman terlambat</p>
                <?php else: ?>
                    <?php foreach ($overduePeminjaman as $p): ?>
                        <div class="p-4 flex items-center justify-between hover:bg-red-50 bg-red-50">
                            <div>
                                <p class="font-medium text-gray-800"><?= e($p['nama_lengkap']) ?></p>
                                <p class="text-sm text-gray-500"><?= e($p['sarpras_nama']) ?></p>
                                <p class="text-xs text-red-600">Jatuh tempo: <?= formatDate($p['tgl_kembali_rencana']) ?></p>
                            </div>
                            <a href="../admin/pengembalian/process.php?kode=<?= urlencode($p['kode_peminjaman']) ?>"
                                class="px-3 py-1 bg-red-500 text-white text-sm rounded-lg hover:bg-red-600">
                                Proses
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Pending Pengaduan -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-bullhorn text-purple-500 mr-2"></i>Pengaduan Aktif
            </h2>
            <a href="../admin/pengaduan/" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
        </div>
        <div class="divide-y">
            <?php if (empty($pendingPengaduan)): ?>
                <p class="p-4 text-gray-500 text-center">Tidak ada pengaduan aktif</p>
            <?php else: ?>
                <?php foreach ($pendingPengaduan as $pg): ?>
                    <div class="p-4 flex items-center justify-between hover:bg-gray-50">
                        <div>
                            <p class="font-medium text-gray-800"><?= e($pg['judul']) ?></p>
                            <p class="text-sm text-gray-500"><?= e($pg['nama_lengkap']) ?> - <?= e($pg['lokasi'] ?? 'Lokasi tidak dicantumkan') ?></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <?= getStatusBadge($pg['status']) ?>
                            <a href="../admin/pengaduan/view.php?id=<?= $pg['id'] ?>"
                                class="px-3 py-1 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600">
                                Detail
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>