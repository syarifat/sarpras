<?php

/**
 * User - Dashboard
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Dashboard');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['user']);

$userId = getCurrentUser()['id'];

// Get user statistics
$stats = [
    'total_peminjaman' => fetch("SELECT COUNT(*) as count FROM peminjaman WHERE user_id = ?", [$userId])['count'],
    'peminjaman_aktif' => fetch("SELECT COUNT(*) as count FROM peminjaman WHERE user_id = ? AND status IN ('approved', 'active')", [$userId])['count'],
    'peminjaman_pending' => fetch("SELECT COUNT(*) as count FROM peminjaman WHERE user_id = ? AND status = 'pending'", [$userId])['count'],
    'pengaduan_aktif' => fetch("SELECT COUNT(*) as count FROM pengaduan WHERE user_id = ? AND status IN ('pending', 'proses')", [$userId])['count'],
];

// Get recent peminjaman
$recentPeminjaman = fetchAll("
    SELECT p.*, s.nama as sarpras_nama, s.kode as sarpras_kode 
    FROM peminjaman p 
    JOIN sarpras s ON p.sarpras_id = s.id 
    WHERE p.user_id = ? 
    ORDER BY p.created_at DESC 
    LIMIT 5
", [$userId]);

// Get active peminjaman
$activePeminjaman = fetchAll("
    SELECT p.*, s.nama as sarpras_nama, s.kode as sarpras_kode 
    FROM peminjaman p 
    JOIN sarpras s ON p.sarpras_id = s.id 
    WHERE p.user_id = ? AND p.status IN ('approved', 'active')
    ORDER BY p.tgl_kembali_rencana ASC
", [$userId]);

// Get recent pengaduan
$recentPengaduan = fetchAll("
    SELECT * FROM pengaduan WHERE user_id = ? ORDER BY created_at DESC LIMIT 5
", [$userId]);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="space-y-6">
    <!-- Welcome -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-lg p-6 text-white">
        <h1 class="text-2xl font-bold">Selamat Datang, <?= e(getCurrentUser()['nama_lengkap']) ?>!</h1>
        <p class="opacity-90 mt-1">Kelola peminjaman sarpras dan pengaduan kerusakan Anda di sini.</p>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-gray-500">Total Peminjaman</p>
                    <p class="text-xl font-bold text-gray-800"><?= $stats['total_peminjaman'] ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-gray-500">Sedang Dipinjam</p>
                    <p class="text-xl font-bold text-gray-800"><?= $stats['peminjaman_aktif'] ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-gray-500">Menunggu Approval</p>
                    <p class="text-xl font-bold text-gray-800"><?= $stats['peminjaman_pending'] ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-red-100 text-red-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-gray-500">Pengaduan Aktif</p>
                    <p class="text-xl font-bold text-gray-800"><?= $stats['pengaduan_aktif'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="peminjaman/create.php" class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow-sm p-6 text-white hover:shadow-lg transition flex items-center">
            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                <i class="fas fa-plus text-2xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold">Ajukan Peminjaman Baru</h3>
                <p class="text-sm opacity-90">Pinjam sarpras untuk kegiatan Anda</p>
            </div>
        </a>
        <a href="pengaduan/create.php" class="bg-gradient-to-r from-red-500 to-rose-600 rounded-xl shadow-sm p-6 text-white hover:shadow-lg transition flex items-center">
            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                <i class="fas fa-bullhorn text-2xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold">Laporkan Kerusakan</h3>
                <p class="text-sm opacity-90">Laporkan sarpras yang rusak</p>
            </div>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Active Peminjaman -->
        <?php if (!empty($activePeminjaman)): ?>
            <div class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>Peminjaman Aktif
                    </h2>
                </div>
                <div class="divide-y">
                    <?php foreach ($activePeminjaman as $p):
                        $isOverdue = strtotime($p['tgl_kembali_rencana']) < strtotime('today');
                    ?>
                        <div class="p-4 <?= $isOverdue ? 'bg-red-50' : '' ?>">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium text-gray-800"><?= e($p['sarpras_nama']) ?></p>
                                    <p class="text-xs text-gray-500"><?= e($p['kode_peminjaman']) ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm <?= $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-600' ?>">
                                        Kembali: <?= formatDate($p['tgl_kembali_rencana'], 'd M Y') ?>
                                    </p>
                                    <?php if ($isOverdue): ?>
                                        <span class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded-full">TERLAMBAT</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Recent Peminjaman -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800">Riwayat Peminjaman</h2>
                <a href="peminjaman/" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
            </div>
            <div class="divide-y">
                <?php if (empty($recentPeminjaman)): ?>
                    <p class="p-6 text-gray-500 text-center">Belum ada peminjaman</p>
                <?php else: ?>
                    <?php foreach ($recentPeminjaman as $p): ?>
                        <div class="p-4 flex justify-between items-center hover:bg-gray-50">
                            <div>
                                <p class="font-medium text-gray-800"><?= e($p['sarpras_nama']) ?></p>
                                <p class="text-xs text-gray-500"><?= formatDate($p['created_at']) ?></p>
                            </div>
                            <?= getStatusBadge($p['status']) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Pengaduan -->
    <?php if (!empty($recentPengaduan)): ?>
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800">Pengaduan Saya</h2>
                <a href="pengaduan/" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
            </div>
            <div class="divide-y">
                <?php foreach ($recentPengaduan as $pg): ?>
                    <div class="p-4 flex justify-between items-center hover:bg-gray-50">
                        <div>
                            <p class="font-medium text-gray-800"><?= e($pg['judul']) ?></p>
                            <p class="text-xs text-gray-500"><?= formatDate($pg['created_at']) ?></p>
                        </div>
                        <?= getStatusBadge($pg['status']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>