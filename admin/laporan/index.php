<?php

/**
 * Admin - Reports
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Laporan');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin']);

// Date range filter
$startDate = sanitize($_GET['start_date'] ?? date('Y-m-01'));
$endDate = sanitize($_GET['end_date'] ?? date('Y-m-d'));

// Statistics
$peminjamanStats = fetch("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status IN ('approved', 'active') THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM peminjaman 
    WHERE DATE(created_at) BETWEEN ? AND ?
", [$startDate, $endDate]);

$pengaduanStats = fetch("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'proses' THEN 1 ELSE 0 END) as proses,
        SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai
    FROM pengaduan 
    WHERE DATE(created_at) BETWEEN ? AND ?
", [$startDate, $endDate]);

$sarprasStats = fetch("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN kondisi = 'baik' THEN 1 ELSE 0 END) as baik,
        SUM(CASE WHEN kondisi = 'rusak_ringan' THEN 1 ELSE 0 END) as rusak_ringan,
        SUM(CASE WHEN kondisi = 'rusak_berat' THEN 1 ELSE 0 END) as rusak_berat
    FROM sarpras
");

// Top borrowed items
$topBorrowed = fetchAll("
    SELECT s.kode, s.nama, k.nama as kategori, COUNT(p.id) as total_pinjam
    FROM sarpras s
    LEFT JOIN kategori_sarpras k ON s.kategori_id = k.id
    LEFT JOIN peminjaman p ON s.id = p.sarpras_id AND DATE(p.created_at) BETWEEN ? AND ?
    GROUP BY s.id
    ORDER BY total_pinjam DESC
    LIMIT 10
", [$startDate, $endDate]);

// Most active borrowers
$topBorrowers = fetchAll("
    SELECT u.nama_lengkap, COUNT(p.id) as total_pinjam
    FROM users u
    LEFT JOIN peminjaman p ON u.id = p.user_id AND DATE(p.created_at) BETWEEN ? AND ?
    WHERE u.role = 'user'
    GROUP BY u.id
    ORDER BY total_pinjam DESC
    LIMIT 10
", [$startDate, $endDate]);

// Recent returns with damage
$damagedReturns = fetchAll("
    SELECT pg.*, pm.kode_peminjaman, s.nama as sarpras_nama, u.nama_lengkap
    FROM pengembalian pg
    JOIN peminjaman pm ON pg.peminjaman_id = pm.id
    JOIN sarpras s ON pm.sarpras_id = s.id
    JOIN users u ON pm.user_id = u.id
    WHERE pg.kondisi_alat IN ('rusak_ringan', 'rusak_berat', 'hilang')
    AND DATE(pg.created_at) BETWEEN ? AND ?
    ORDER BY pg.created_at DESC
    LIMIT 10
", [$startDate, $endDate]);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Laporan</h1>
            <p class="text-gray-600">Ringkasan data dan statistik sistem</p>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="<?= e($startDate) ?>"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="<?= e($endDate) ?>"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-filter mr-2"></i>Filter
            </button>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Export Data</h3>
        <div class="flex flex-wrap gap-3">
            <a href="export.php?type=peminjaman&format=csv&start_date=<?= e($startDate) ?>&end_date=<?= e($endDate) ?>"
                class="inline-flex items-center px-3 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition text-sm">
                <i class="fas fa-file-csv mr-2"></i>Peminjaman (CSV)
            </a>
            <a href="export.php?type=pengaduan&format=csv&start_date=<?= e($startDate) ?>&end_date=<?= e($endDate) ?>"
                class="inline-flex items-center px-3 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition text-sm">
                <i class="fas fa-file-csv mr-2"></i>Pengaduan (CSV)
            </a>
            <a href="export.php?type=sarpras&format=csv"
                class="inline-flex items-center px-3 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition text-sm">
                <i class="fas fa-file-csv mr-2"></i>Inventaris (CSV)
            </a>
            <a href="export.php?type=maintenance&format=csv&start_date=<?= e($startDate) ?>&end_date=<?= e($endDate) ?>"
                class="inline-flex items-center px-3 py-2 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition text-sm">
                <i class="fas fa-file-csv mr-2"></i>Maintenance (CSV)
            </a>
            <a href="export.php?type=peminjaman&format=print&start_date=<?= e($startDate) ?>&end_date=<?= e($endDate) ?>"
                target="_blank"
                class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm">
                <i class="fas fa-print mr-2"></i>Cetak Peminjaman
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Peminjaman Stats -->
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl shadow-sm p-6 text-white">
            <h3 class="text-lg font-semibold mb-4">Peminjaman</h3>
            <div class="text-4xl font-bold mb-4"><?= $peminjamanStats['total'] ?></div>
            <div class="grid grid-cols-2 gap-2 text-sm">
                <div>Pending: <?= $peminjamanStats['pending'] ?></div>
                <div>Aktif: <?= $peminjamanStats['active'] ?></div>
                <div>Dikembalikan: <?= $peminjamanStats['returned'] ?></div>
                <div>Ditolak: <?= $peminjamanStats['rejected'] ?></div>
            </div>
        </div>

        <!-- Pengaduan Stats -->
        <div class="bg-gradient-to-r from-purple-500 to-violet-600 rounded-xl shadow-sm p-6 text-white">
            <h3 class="text-lg font-semibold mb-4">Pengaduan</h3>
            <div class="text-4xl font-bold mb-4"><?= $pengaduanStats['total'] ?></div>
            <div class="grid grid-cols-2 gap-2 text-sm">
                <div>Pending: <?= $pengaduanStats['pending'] ?></div>
                <div>Diproses: <?= $pengaduanStats['proses'] ?></div>
                <div>Selesai: <?= $pengaduanStats['selesai'] ?></div>
            </div>
        </div>

        <!-- Sarpras Stats -->
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow-sm p-6 text-white">
            <h3 class="text-lg font-semibold mb-4">Kondisi Sarpras</h3>
            <div class="text-4xl font-bold mb-4"><?= $sarprasStats['total'] ?></div>
            <div class="grid grid-cols-2 gap-2 text-sm">
                <div>Baik: <?= $sarprasStats['baik'] ?></div>
                <div>Rusak Ringan: <?= $sarprasStats['rusak_ringan'] ?></div>
                <div>Rusak Berat: <?= $sarprasStats['rusak_berat'] ?></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Borrowed Items -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b">
                <h2 class="text-lg font-semibold text-gray-800">Sarpras Paling Sering Dipinjam</h2>
            </div>
            <div class="divide-y">
                <?php if (empty($topBorrowed)): ?>
                    <p class="p-4 text-gray-500 text-center">Tidak ada data</p>
                <?php else: ?>
                    <?php foreach ($topBorrowed as $i => $item): ?>
                        <div class="p-4 flex items-center justify-between hover:bg-gray-50">
                            <div class="flex items-center">
                                <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold mr-3">
                                    <?= $i + 1 ?>
                                </span>
                                <div>
                                    <p class="font-medium text-gray-800"><?= e($item['nama']) ?></p>
                                    <p class="text-xs text-gray-500"><?= e($item['kategori']) ?></p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                                <?= $item['total_pinjam'] ?>x
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Borrowers -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b">
                <h2 class="text-lg font-semibold text-gray-800">Peminjam Paling Aktif</h2>
            </div>
            <div class="divide-y">
                <?php if (empty($topBorrowers)): ?>
                    <p class="p-4 text-gray-500 text-center">Tidak ada data</p>
                <?php else: ?>
                    <?php foreach ($topBorrowers as $i => $user): ?>
                        <div class="p-4 flex items-center justify-between hover:bg-gray-50">
                            <div class="flex items-center">
                                <span class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center font-bold mr-3">
                                    <?= $i + 1 ?>
                                </span>
                                <p class="font-medium text-gray-800"><?= e($user['nama_lengkap']) ?></p>
                            </div>
                            <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded-full">
                                <?= $user['total_pinjam'] ?> peminjaman
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Damaged Returns -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>Pengembalian dengan Kerusakan
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peminjam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sarpras</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl Kembali</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kondisi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (empty($damagedReturns)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data pengembalian rusak</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($damagedReturns as $r): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-mono text-sm"><?= e($r['kode_peminjaman']) ?></td>
                                <td class="px-6 py-4"><?= e($r['nama_lengkap']) ?></td>
                                <td class="px-6 py-4"><?= e($r['sarpras_nama']) ?></td>
                                <td class="px-6 py-4 text-sm"><?= formatDate($r['tgl_pengembalian']) ?></td>
                                <td class="px-6 py-4"><?= getConditionBadge($r['kondisi_alat']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600"><?= e($r['deskripsi_kerusakan'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>