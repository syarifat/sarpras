<?php

/**
 * Admin - Maintenance History
 * Sarpras Management System - Tier 2
 */

define('PAGE_TITLE', 'Riwayat Maintenance');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin', 'petugas']);

// Filters
$search = sanitize($_GET['search'] ?? '');
$hasil = sanitize($_GET['hasil'] ?? '');
$startDate = sanitize($_GET['start_date'] ?? date('Y-m-01'));
$endDate = sanitize($_GET['end_date'] ?? date('Y-m-d'));

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;

// Build query
$where = ["ml.tgl_maintenance BETWEEN ? AND ?"];
$params = [$startDate, $endDate];

if ($search) {
    $where[] = "(s.nama LIKE ? OR s.kode LIKE ? OR ml.jenis_maintenance LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($hasil) {
    $where[] = "ml.hasil = ?";
    $params[] = $hasil;
}

$whereClause = implode(' AND ', $where);

// Get total count
$countSql = "SELECT COUNT(*) as count FROM maintenance_log ml JOIN sarpras s ON ml.sarpras_id = s.id WHERE $whereClause";
$total = fetch($countSql, $params)['count'];
$pagination = paginate($total, $page, $perPage);

// Get logs
$logs = fetchAll("
    SELECT ml.*, s.kode as sarpras_kode, s.nama as sarpras_nama, u.nama_lengkap as performed_by_name
    FROM maintenance_log ml
    JOIN sarpras s ON ml.sarpras_id = s.id
    LEFT JOIN users u ON ml.performed_by = u.id
    WHERE $whereClause
    ORDER BY ml.tgl_maintenance DESC
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
", $params);

// Get stats
$stats = fetch("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN hasil = 'berhasil' THEN 1 ELSE 0 END) as berhasil,
        SUM(CASE WHEN hasil = 'perlu_perbaikan' THEN 1 ELSE 0 END) as perlu_perbaikan,
        SUM(CASE WHEN hasil = 'gagal' THEN 1 ELSE 0 END) as gagal,
        SUM(biaya) as total_biaya
    FROM maintenance_log 
    WHERE tgl_maintenance BETWEEN ? AND ?
", [$startDate, $endDate]);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Riwayat Maintenance</h1>
            <p class="text-gray-600">Log semua pelaksanaan maintenance</p>
        </div>
        <a href="index.php" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-gray-800"><?= $stats['total'] ?></p>
            <p class="text-xs text-gray-500">Total</p>
        </div>
        <div class="bg-green-50 rounded-lg shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-green-600"><?= $stats['berhasil'] ?></p>
            <p class="text-xs text-gray-500">Berhasil</p>
        </div>
        <div class="bg-yellow-50 rounded-lg shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-yellow-600"><?= $stats['perlu_perbaikan'] ?></p>
            <p class="text-xs text-gray-500">Perlu Perbaikan</p>
        </div>
        <div class="bg-red-50 rounded-lg shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-red-600"><?= $stats['gagal'] ?></p>
            <p class="text-xs text-gray-500">Gagal</p>
        </div>
        <div class="bg-blue-50 rounded-lg shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">Rp <?= number_format($stats['total_biaya'] ?? 0, 0, ',', '.') ?></p>
            <p class="text-xs text-gray-500">Total Biaya</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Nama sarpras..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hasil</label>
                <select name="hasil" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    <option value="berhasil" <?= $hasil === 'berhasil' ? 'selected' : '' ?>>Berhasil</option>
                    <option value="perlu_perbaikan" <?= $hasil === 'perlu_perbaikan' ? 'selected' : '' ?>>Perlu Perbaikan</option>
                    <option value="gagal" <?= $hasil === 'gagal' ? 'selected' : '' ?>>Gagal</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari</label>
                <input type="date" name="start_date" value="<?= e($startDate) ?>"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai</label>
                <input type="date" name="end_date" value="<?= e($endDate) ?>"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-search mr-2"></i>Filter
            </button>
        </form>
    </div>

    <!-- Log Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sarpras</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hasil</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Biaya</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">Tidak ada data</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm"><?= formatDate($log['tgl_maintenance'], 'd M Y') ?></td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800"><?= e($log['sarpras_nama']) ?></p>
                                    <p class="text-xs text-gray-500"><?= e($log['sarpras_kode']) ?></p>
                                </td>
                                <td class="px-4 py-3 text-sm"><?= ucfirst(str_replace('_', ' ', $log['jenis_maintenance'])) ?></td>
                                <td class="px-4 py-3">
                                    <?php if ($log['hasil'] === 'berhasil'): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Berhasil</span>
                                    <?php elseif ($log['hasil'] === 'perlu_perbaikan'): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Perlu Perbaikan</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Gagal</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-right">Rp <?= number_format($log['biaya'], 0, ',', '.') ?></td>
                                <td class="px-4 py-3 text-sm text-gray-600"><?= e($log['performed_by_name'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <div class="px-6 py-4 border-t flex items-center justify-between">
                <p class="text-sm text-gray-600">
                    Menampilkan <?= $pagination['offset'] + 1 ?> - <?= min($pagination['offset'] + $pagination['per_page'], $total) ?> dari <?= $total ?>
                </p>
                <div class="flex gap-2">
                    <?php if ($pagination['has_prev']): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="px-3 py-1 border rounded hover:bg-gray-100">Prev</a>
                    <?php endif; ?>
                    <?php if ($pagination['has_next']): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="px-3 py-1 border rounded hover:bg-gray-100">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>