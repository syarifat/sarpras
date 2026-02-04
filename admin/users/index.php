<?php

/**
 * Admin - User List
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Kelola Pengguna');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin']);

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$search = sanitize($_GET['search'] ?? '');
$roleFilter = sanitize($_GET['role'] ?? '');

// Build query
$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (username LIKE ? OR nama_lengkap LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($roleFilter) {
    $where .= " AND role = ?";
    $params[] = $roleFilter;
}

$totalItems = fetch("SELECT COUNT(*) as count FROM users $where", $params)['count'];
$pagination = paginate($totalItems, $page, $perPage);

$users = fetchAll("
    SELECT * FROM users 
    $where 
    ORDER BY created_at DESC 
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
", $params);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Kelola Pengguna</h1>
            <p class="text-gray-600">Manajemen data pengguna sistem</p>
        </div>
        <a href="create.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
            <i class="fas fa-plus mr-2"></i>Tambah Pengguna
        </a>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="<?= e($search) ?>"
                    placeholder="Cari username, nama, atau email..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="w-full sm:w-48">
                <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Role</option>
                    <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="petugas" <?= $roleFilter === 'petugas' ? 'selected' : '' ?>>Petugas</option>
                    <option value="user" <?= $roleFilter === 'user' ? 'selected' : '' ?>>User</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                <i class="fas fa-search mr-2"></i>Cari
            </button>
            <?php if ($search || $roleFilter): ?>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Lengkap</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Dibuat</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3 block"></i>
                                Tidak ada data pengguna
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $i => $user): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= $pagination['offset'] + $i + 1 ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-medium text-gray-800"><?= e($user['username']) ?></span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-800">
                                    <?= e($user['nama_lengkap']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= e($user['email'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $roleBadges = [
                                        'admin' => 'bg-red-100 text-red-800',
                                        'petugas' => 'bg-blue-100 text-blue-800',
                                        'user' => 'bg-green-100 text-green-800',
                                    ];
                                    $badgeClass = $roleBadges[$user['role']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $badgeClass ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= formatDate($user['created_at']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="edit.php?id=<?= $user['id'] ?>"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['id'] != $currentUser['id']): ?>
                                            <a href="delete.php?id=<?= $user['id'] ?>"
                                                onclick="return confirm('Yakin ingin menghapus pengguna ini?')"
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                                <i class="fas fa-trash"></i>
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
                            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>"
                                class="px-3 py-1 bg-white border rounded hover:bg-gray-50">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php for ($p = max(1, $page - 2); $p <= min($pagination['total_pages'], $page + 2); $p++): ?>
                            <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>"
                                class="px-3 py-1 border rounded <?= $p === $page ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-50' ?>">
                                <?= $p ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($pagination['has_next']): ?>
                            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>"
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