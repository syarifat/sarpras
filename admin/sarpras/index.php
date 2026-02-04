<?php

/**
 * Admin - Sarpras List
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Kelola Sarpras');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin']);

// Pagination and filters
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$search = sanitize($_GET['search'] ?? '');
$kategoriFilter = intval($_GET['kategori'] ?? 0);
$kondisiFilter = sanitize($_GET['kondisi'] ?? '');

// Build query
$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (s.kode LIKE ? OR s.nama LIKE ? OR s.lokasi LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($kategoriFilter) {
    $where .= " AND s.kategori_id = ?";
    $params[] = $kategoriFilter;
}

if ($kondisiFilter) {
    $where .= " AND s.kondisi = ?";
    $params[] = $kondisiFilter;
}

$totalItems = fetch("SELECT COUNT(*) as count FROM sarpras s $where", $params)['count'];
$pagination = paginate($totalItems, $page, $perPage);

$sarpras = fetchAll("
    SELECT s.*, k.nama as kategori_nama 
    FROM sarpras s 
    LEFT JOIN kategori_sarpras k ON s.kategori_id = k.id 
    $where 
    ORDER BY s.created_at DESC 
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
", $params);

$kategoriList = fetchAll("SELECT * FROM kategori_sarpras ORDER BY nama ASC");

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Kelola Sarpras</h1>
            <p class="text-gray-600">Manajemen data sarana prasarana</p>
        </div>
        <a href="create.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
            <i class="fas fa-plus mr-2"></i>Tambah Sarpras
        </a>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-col lg:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="<?= e($search) ?>"
                    placeholder="Cari kode, nama, atau lokasi..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="w-full lg:w-48">
                <select name="kategori" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategoriList as $kat): ?>
                        <option value="<?= $kat['id'] ?>" <?= $kategoriFilter == $kat['id'] ? 'selected' : '' ?>><?= e($kat['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="w-full lg:w-40">
                <select name="kondisi" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Kondisi</option>
                    <option value="baik" <?= $kondisiFilter === 'baik' ? 'selected' : '' ?>>Baik</option>
                    <option value="rusak_ringan" <?= $kondisiFilter === 'rusak_ringan' ? 'selected' : '' ?>>Rusak Ringan</option>
                    <option value="rusak_berat" <?= $kondisiFilter === 'rusak_berat' ? 'selected' : '' ?>>Rusak Berat</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                <i class="fas fa-search mr-2"></i>Cari
            </button>
            <?php if ($search || $kategoriFilter || $kondisiFilter): ?>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stok</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kondisi</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($sarpras)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3 block"></i>
                                Tidak ada data sarpras
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sarpras as $s): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded"><?= e($s['kode']) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-800"><?= e($s['nama']) ?></p>
                                        <?php if ($s['deskripsi']): ?>
                                            <p class="text-xs text-gray-500 truncate max-w-xs"><?= e($s['deskripsi']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= e($s['kategori_nama'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= e($s['lokasi'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 text-sm font-semibold rounded-full <?= $s['jumlah_stok'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $s['jumlah_stok'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?= getConditionBadge($s['kondisi']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="view.php?id=<?= $s['id'] ?>"
                                            class="p-2 text-gray-600 hover:bg-gray-50 rounded-lg transition" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit.php?id=<?= $s['id'] ?>"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $s['id'] ?>"
                                            onclick="return confirm('Yakin ingin menghapus sarpras ini?')"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                            <i class="fas fa-trash"></i>
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
                            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&kategori=<?= $kategoriFilter ?>&kondisi=<?= urlencode($kondisiFilter) ?>"
                                class="px-3 py-1 bg-white border rounded hover:bg-gray-50">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php for ($p = max(1, $page - 2); $p <= min($pagination['total_pages'], $page + 2); $p++): ?>
                            <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&kategori=<?= $kategoriFilter ?>&kondisi=<?= urlencode($kondisiFilter) ?>"
                                class="px-3 py-1 border rounded <?= $p === $page ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-50' ?>">
                                <?= $p ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($pagination['has_next']): ?>
                            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&kategori=<?= $kategoriFilter ?>&kondisi=<?= urlencode($kondisiFilter) ?>"
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