<?php

/**
 * Admin Dashboard
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Dashboard Admin');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['admin']);

// Get statistics
$stats = [
    'total_users' => fetch("SELECT COUNT(*) as count FROM users")['count'],
    'total_sarpras' => fetch("SELECT COUNT(*) as count FROM sarpras")['count'],
    'total_kategori' => fetch("SELECT COUNT(*) as count FROM kategori_sarpras")['count'],
    'peminjaman_pending' => fetch("SELECT COUNT(*) as count FROM peminjaman WHERE status = 'pending'")['count'],
    'peminjaman_active' => fetch("SELECT COUNT(*) as count FROM peminjaman WHERE status IN ('approved', 'active')")['count'],
    'pengaduan_pending' => fetch("SELECT COUNT(*) as count FROM pengaduan WHERE status = 'pending'")['count'],
    'sarpras_rusak' => fetch("SELECT COUNT(*) as count FROM sarpras WHERE kondisi IN ('rusak_ringan', 'rusak_berat')")['count'],
];

// Recent activities
$recentActivities = fetchAll("
    SELECT al.*, u.nama_lengkap 
    FROM activity_log al 
    LEFT JOIN users u ON al.user_id = u.id 
    ORDER BY al.created_at DESC 
    LIMIT 10
");

// Recent borrowings
$recentPeminjaman = fetchAll("
    SELECT p.*, u.nama_lengkap, s.nama as sarpras_nama 
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN sarpras s ON p.sarpras_id = s.id 
    ORDER BY p.created_at DESC 
    LIMIT 5
");

// Recent complaints
$recentPengaduan = fetchAll("
    SELECT pg.*, u.nama_lengkap 
    FROM pengaduan pg 
    JOIN users u ON pg.user_id = u.id 
    ORDER BY pg.created_at DESC 
    LIMIT 5
");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Dashboard Admin</h1>
            <p class="text-gray-600">Selamat datang, <?= e($currentUser['nama_lengkap']) ?>!</p>
        </div>
        <div class="text-sm text-gray-500">
            <i class="fas fa-calendar-alt mr-1"></i>
            <?= date('l, d F Y') ?>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Users -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Pengguna</p>
                    <p class="text-2xl font-bold text-gray-800"><?= number_format($stats['total_users']) ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Sarpras -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Sarpras</p>
                    <p class="text-2xl font-bold text-gray-800"><?= number_format($stats['total_sarpras']) ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-boxes text-green-500 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Peminjaman Pending -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Menunggu Approval</p>
                    <p class="text-2xl font-bold text-gray-800"><?= number_format($stats['peminjaman_pending']) ?></p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-500 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Pengaduan Pending -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Pengaduan Baru</p>
                    <p class="text-2xl font-bold text-gray-800"><?= number_format($stats['pengaduan_pending']) ?></p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-tags text-purple-500"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Kategori</p>
                    <p class="text-xl font-bold text-gray-800"><?= number_format($stats['total_kategori']) ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-hand-holding text-green-500"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Peminjaman Aktif</p>
                    <p class="text-xl font-bold text-gray-800"><?= number_format($stats['peminjaman_active']) ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-tools text-red-500"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Sarpras Rusak</p>
                    <p class="text-xl font-bold text-gray-800"><?= number_format($stats['sarpras_rusak']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Borrowings -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-hand-holding text-blue-500 mr-2"></i>Peminjaman Terbaru
                    </h2>
                    <a href="/sarpras_lagi/admin/peminjaman/" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
                </div>
            </div>
            <div class="divide-y">
                <?php if (empty($recentPeminjaman)): ?>
                    <div class="p-6 text-center text-gray-500">Belum ada data peminjaman</div>
                <?php else: ?>
                    <?php foreach ($recentPeminjaman as $p): ?>
                        <div class="p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-800"><?= e($p['sarpras_nama']) ?></p>
                                    <p class="text-sm text-gray-500"><?= e($p['nama_lengkap']) ?> • <?= e($p['kode_peminjaman']) ?></p>
                                </div>
                                <div class="text-right">
                                    <?= getStatusBadge($p['status']) ?>
                                    <p class="text-xs text-gray-400 mt-1"><?= formatDate($p['tgl_pinjam']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Complaints -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>Pengaduan Terbaru
                    </h2>
                    <a href="/sarpras_lagi/admin/pengaduan/" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
                </div>
            </div>
            <div class="divide-y">
                <?php if (empty($recentPengaduan)): ?>
                    <div class="p-6 text-center text-gray-500">Belum ada data pengaduan</div>
                <?php else: ?>
                    <?php foreach ($recentPengaduan as $pg): ?>
                        <div class="p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-800"><?= e($pg['judul']) ?></p>
                                    <p class="text-sm text-gray-500"><?= e($pg['nama_lengkap']) ?> • <?= e($pg['lokasi']) ?></p>
                                </div>
                                <div class="text-right">
                                    <?= getStatusBadge($pg['status']) ?>
                                    <p class="text-xs text-gray-400 mt-1"><?= formatDate($pg['created_at']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Activity Log -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-history text-gray-500 mr-2"></i>Aktivitas Terbaru
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pengguna</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($recentActivities as $activity): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= formatDateTime($activity['created_at']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                <?= e($activity['nama_lengkap'] ?? 'System') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                    <?= e($activity['aksi']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?= e($activity['deskripsi']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>