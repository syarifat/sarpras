<?php

/**
 * Admin - Pengembalian List
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Kelola Pengembalian');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin', 'petugas']);

// Get active borrowings (need to be returned)
$activePeminjaman = fetchAll("
    SELECT p.*, u.nama_lengkap, s.nama as sarpras_nama, s.kode as sarpras_kode
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN sarpras s ON p.sarpras_id = s.id 
    WHERE p.status IN ('approved', 'active')
    ORDER BY p.tgl_kembali_rencana ASC
");

// Get recent returns
$recentReturns = fetchAll("
    SELECT pg.*, pm.kode_peminjaman, u.nama_lengkap, s.nama as sarpras_nama, 
           pr.nama_lengkap as processed_by_name
    FROM pengembalian pg 
    JOIN peminjaman pm ON pg.peminjaman_id = pm.id 
    JOIN users u ON pm.user_id = u.id 
    JOIN sarpras s ON pm.sarpras_id = s.id 
    LEFT JOIN users pr ON pg.diterima_oleh = pr.id
    ORDER BY pg.created_at DESC 
    LIMIT 20
");

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Kelola Pengembalian</h1>
            <p class="text-gray-600">Proses pengembalian dan pencatatan kondisi alat</p>
        </div>
        <a href="process.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
            <i class="fas fa-qrcode mr-2"></i>Scan QR / Input Kode
        </a>
    </div>

    <!-- Active Borrowings -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-clock text-yellow-500 mr-2"></i>Peminjaman Aktif (Menunggu Dikembalikan)
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peminjam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sarpras</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl Pinjam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jatuh Tempo</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($activePeminjaman)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-check-circle text-4xl mb-3 block text-green-500"></i>
                                Tidak ada peminjaman aktif
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($activePeminjaman as $p):
                            $isOverdue = strtotime($p['tgl_kembali_rencana']) < strtotime('today');
                        ?>
                            <tr class="hover:bg-gray-50 <?= $isOverdue ? 'bg-red-50' : '' ?>">
                                <td class="px-6 py-4">
                                    <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded"><?= e($p['kode_peminjaman']) ?></span>
                                </td>
                                <td class="px-6 py-4 font-medium"><?= e($p['nama_lengkap']) ?></td>
                                <td class="px-6 py-4">
                                    <p class="font-medium"><?= e($p['sarpras_nama']) ?></p>
                                    <p class="text-xs text-gray-500"><?= e($p['sarpras_kode']) ?></p>
                                </td>
                                <td class="px-6 py-4 text-sm"><?= formatDate($p['tgl_pinjam']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="<?= $isOverdue ? 'text-red-600 font-semibold' : '' ?>">
                                        <?= formatDate($p['tgl_kembali_rencana']) ?>
                                        <?php if ($isOverdue): ?>
                                            <span class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded-full ml-1">TERLAMBAT</span>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="process.php?kode=<?= urlencode($p['kode_peminjaman']) ?>"
                                        class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition">
                                        <i class="fas fa-undo mr-1"></i>Proses Kembali
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Returns -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-history text-blue-500 mr-2"></i>Riwayat Pengembalian Terbaru
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Peminjaman</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peminjam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sarpras</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl Kembali</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kondisi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Diproses Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($recentReturns)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada riwayat pengembalian</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentReturns as $r): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-mono text-sm"><?= e($r['kode_peminjaman']) ?></td>
                                <td class="px-6 py-4"><?= e($r['nama_lengkap']) ?></td>
                                <td class="px-6 py-4"><?= e($r['sarpras_nama']) ?></td>
                                <td class="px-6 py-4 text-sm"><?= formatDate($r['tgl_pengembalian']) ?></td>
                                <td class="px-6 py-4"><?= getConditionBadge($r['kondisi_alat']) ?></td>
                                <td class="px-6 py-4 text-sm"><?= e($r['processed_by_name'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>