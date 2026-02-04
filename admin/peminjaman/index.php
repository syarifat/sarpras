<?php

/**
 * Admin - Peminjaman List
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Kelola Peminjaman');

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
    $where .= " AND (p.kode_peminjaman LIKE ? OR u.nama_lengkap LIKE ? OR s.nama LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($statusFilter) {
    $where .= " AND p.status = ?";
    $params[] = $statusFilter;
}

$totalItems = fetch("
    SELECT COUNT(*) as count 
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN sarpras s ON p.sarpras_id = s.id 
    $where
", $params)['count'];

$pagination = paginate($totalItems, $page, $perPage);

$peminjaman = fetchAll("
    SELECT p.*, u.nama_lengkap, s.nama as sarpras_nama, s.kode as sarpras_kode,
           a.nama_lengkap as approved_by_name
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN sarpras s ON p.sarpras_id = s.id 
    LEFT JOIN users a ON p.approved_by = a.id
    $where 
    ORDER BY 
        CASE p.status 
            WHEN 'pending' THEN 1 
            WHEN 'active' THEN 2
            WHEN 'approved' THEN 3
            ELSE 4 
        END,
        p.created_at DESC 
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
", $params);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Kelola Peminjaman</h1>
            <p class="text-gray-600">Manajemen data peminjaman sarpras</p>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="<?= e($search) ?>"
                    placeholder="Cari kode, peminjam, atau sarpras..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="w-full sm:w-48">
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Menunggu</option>
                    <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                    <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Aktif</option>
                    <option value="returned" <?= $statusFilter === 'returned' ? 'selected' : '' ?>>Dikembalikan</option>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peminjam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sarpras</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl Pinjam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl Kembali</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($peminjaman)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3 block"></i>
                                Tidak ada data peminjaman
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($peminjaman as $p): ?>
                            <tr class="hover:bg-gray-50 <?= $p['status'] === 'pending' ? 'bg-yellow-50' : '' ?>">
                                <td class="px-6 py-4">
                                    <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded"><?= e($p['kode_peminjaman']) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-800"><?= e($p['nama_lengkap']) ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-800"><?= e($p['sarpras_nama']) ?></p>
                                    <p class="text-xs text-gray-500"><?= e($p['sarpras_kode']) ?></p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?= $p['jumlah'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm"><?= formatDate($p['tgl_pinjam']) ?></td>
                                <td class="px-6 py-4 text-sm"><?= formatDate($p['tgl_kembali_rencana']) ?></td>
                                <td class="px-6 py-4"><?= getStatusBadge($p['status']) ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="view.php?id=<?= $p['id'] ?>"
                                            class="p-2 text-gray-600 hover:bg-gray-50 rounded-lg transition" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($p['status'] === 'pending'): ?>
                                            <a href="approve.php?id=<?= $p['id'] ?>"
                                                class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition" title="Proses">
                                                <i class="fas fa-check-circle"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($p['status'] === 'approved' || $p['status'] === 'active'): ?>
                                            <a href="receipt.php?id=<?= $p['id'] ?>"
                                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Cetak Bukti">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        <?php endif; ?>
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