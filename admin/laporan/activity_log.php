<?php

/**
 * Admin - Activity Log
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Activity Log');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin']);

// Filters
$search = sanitize($_GET['search'] ?? '');
$aksi = sanitize($_GET['aksi'] ?? '');
$startDate = sanitize($_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days')));
$endDate = sanitize($_GET['end_date'] ?? date('Y-m-d'));

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 25;

// Build query
$where = ["DATE(al.created_at) BETWEEN ? AND ?"];
$params = [$startDate, $endDate];

if ($search) {
    $where[] = "(u.nama_lengkap LIKE ? OR u.username LIKE ? OR al.deskripsi LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($aksi) {
    $where[] = "al.aksi = ?";
    $params[] = $aksi;
}

$whereClause = implode(' AND ', $where);

// Get total count
$countSql = "SELECT COUNT(*) as count FROM activity_log al LEFT JOIN users u ON al.user_id = u.id WHERE $whereClause";
$total = fetch($countSql, $params)['count'];
$pagination = paginate($total, $page, $perPage);

// Get log entries
$sql = "SELECT al.*, u.nama_lengkap, u.username, u.role 
        FROM activity_log al 
        LEFT JOIN users u ON al.user_id = u.id 
        WHERE $whereClause 
        ORDER BY al.created_at DESC 
        LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";
$logs = fetchAll($sql, $params);

// Get unique actions for filter
$actions = fetchAll("SELECT DISTINCT aksi FROM activity_log ORDER BY aksi");

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Activity Log</h1>
            <p class="text-gray-600">Riwayat aktivitas pengguna sistem</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Nama, username, atau deskripsi..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Aksi</label>
                <select name="aksi" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Aksi</option>
                    <?php foreach ($actions as $a): ?>
                        <option value="<?= e($a['aksi']) ?>" <?= $aksi === $a['aksi'] ? 'selected' : '' ?>><?= e($a['aksi']) ?></option>
                    <?php endforeach; ?>
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
            <a href="activity_log.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Reset</a>
        </form>
    </div>

    <!-- Log Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3 block"></i>
                                Tidak ada data activity log
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                                    <?= formatDateTime($log['created_at'], 'd M Y H:i:s') ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($log['nama_lengkap']): ?>
                                        <p class="font-medium text-gray-800"><?= e($log['nama_lengkap']) ?></p>
                                        <p class="text-xs text-gray-500"><?= e($log['username']) ?> (<?= ucfirst($log['role']) ?>)</p>
                                    <?php else: ?>
                                        <span class="text-gray-400">System</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $aksiColors = [
                                        'LOGIN' => 'bg-green-100 text-green-800',
                                        'LOGOUT' => 'bg-gray-100 text-gray-800',
                                        'CREATE_PEMINJAMAN' => 'bg-blue-100 text-blue-800',
                                        'APPROVE_PEMINJAMAN' => 'bg-emerald-100 text-emerald-800',
                                        'REJECT_PEMINJAMAN' => 'bg-red-100 text-red-800',
                                        'PROCESS_RETURN' => 'bg-purple-100 text-purple-800',
                                        'CREATE_PENGADUAN' => 'bg-yellow-100 text-yellow-800',
                                    ];
                                    $color = $aksiColors[$log['aksi']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $color ?>">
                                        <?= e($log['aksi']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="<?= e($log['deskripsi']) ?>">
                                    <?= e($log['deskripsi']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 font-mono">
                                    <?= e($log['ip_address']) ?>
                                </td>
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
                    Menampilkan <?= $pagination['offset'] + 1 ?> - <?= min($pagination['offset'] + $pagination['per_page'], $total) ?> dari <?= $total ?> data
                </p>
                <div class="flex gap-2">
                    <?php if ($pagination['has_prev']): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"
                            class="px-3 py-1 border rounded hover:bg-gray-100">Prev</a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($pagination['total_pages'], $page + 2); $i++): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                            class="px-3 py-1 border rounded <?= $i === $page ? 'bg-blue-600 text-white' : 'hover:bg-gray-100' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($pagination['has_next']): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"
                            class="px-3 py-1 border rounded hover:bg-gray-100">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>