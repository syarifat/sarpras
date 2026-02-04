<?php

/**
 * Admin - Pengaduan List
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Kelola Pengaduan');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin', 'petugas']);

// Pagination and filters
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$search = sanitize($_GET['search'] ?? '');
$statusFilter = sanitize($_GET['status'] ?? '');

// Build query
$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (pg.judul LIKE ? OR u.nama_lengkap LIKE ? OR pg.lokasi LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($statusFilter) {
    $where .= " AND pg.status = ?";
    $params[] = $statusFilter;
}

$totalItems = fetch("
    SELECT COUNT(*) as count 
    FROM pengaduan pg 
    JOIN users u ON pg.user_id = u.id 
    $where
", $params)['count'];

$pagination = paginate($totalItems, $page, $perPage);

$pengaduan = fetchAll("
    SELECT pg.*, u.nama_lengkap,
           (SELECT COUNT(*) FROM catatan_pengaduan WHERE pengaduan_id = pg.id) as jumlah_catatan
    FROM pengaduan pg 
    JOIN users u ON pg.user_id = u.id 
    $where 
    ORDER BY 
        CASE pg.status WHEN 'pending' THEN 1 WHEN 'proses' THEN 2 ELSE 3 END,
        pg.created_at DESC 
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
", $params);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Kelola Pengaduan</h1>
            <p class="text-gray-600">Manajemen pengaduan kerusakan sarpras</p>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="<?= e($search) ?>"
                    placeholder="Cari judul, pelapor, atau lokasi..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="w-full sm:w-48">
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Menunggu</option>
                    <option value="proses" <?= $statusFilter === 'proses' ? 'selected' : '' ?>>Diproses</option>
                    <option value="selesai" <?= $statusFilter === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="ditutup" <?= $statusFilter === 'ditutup' ? 'selected' : '' ?>>Ditutup</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                <i class="fas fa-search mr-2"></i>Cari
            </button>
            <?php if ($search || $statusFilter): ?>
                <a href="index.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-center">
                    <i class="fas fa-times mr-2"></i>Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelapor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl Lapor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Catatan</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($pengaduan)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3 block"></i>
                                Tidak ada data pengaduan
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pengaduan as $pg): ?>
                            <tr class="hover:bg-gray-50 <?= $pg['status'] === 'pending' ? 'bg-yellow-50' : '' ?>">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-800"><?= e($pg['judul']) ?></p>
                                    <p class="text-xs text-gray-500 truncate max-w-xs"><?= e(substr($pg['deskripsi'], 0, 60)) ?>...</p>
                                </td>
                                <td class="px-6 py-4"><?= e($pg['nama_lengkap']) ?></td>
                                <td class="px-6 py-4 text-sm"><?= e($pg['lokasi'] ?? '-') ?></td>
                                <td class="px-6 py-4 text-sm"><?= formatDate($pg['created_at']) ?></td>
                                <td class="px-6 py-4"><?= getStatusBadge($pg['status']) ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        <?= $pg['jumlah_catatan'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="view.php?id=<?= $pg['id'] ?>"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Detail/Proses">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <div class="px-6 py-4 border-t bg-gray-50">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500">
                        Menampilkan <?= $pagination['offset'] + 1 ?> - <?= min($pagination['offset'] + $pagination['per_page'], $pagination['total_items']) ?>
                        dari <?= $pagination['total_items'] ?> data
                    </p>
                    <div class="flex space-x-2">
                        <?php if ($pagination['has_prev']): ?>
                            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>"
                                class="px-3 py-1 bg-white border rounded hover:bg-gray-50">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php for ($pp = max(1, $page - 2); $pp <= min($pagination['total_pages'], $page + 2); $pp++): ?>
                            <a href="?page=<?= $pp ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>"
                                class="px-3 py-1 border rounded <?= $pp === $page ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-50' ?>">
                                <?= $pp ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($pagination['has_next']): ?>
                            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>"
                                class="px-3 py-1 bg-white border rounded hover:bg-gray-50">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>