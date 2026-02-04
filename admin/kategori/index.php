<?php

/**
 * Admin - Kategori List
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Kelola Kategori');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin']);

// Get all categories with item count
$kategori = fetchAll("
    SELECT k.*, COUNT(s.id) as total_sarpras 
    FROM kategori_sarpras k 
    LEFT JOIN sarpras s ON k.id = s.kategori_id 
    GROUP BY k.id 
    ORDER BY k.nama ASC
");

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Kelola Kategori</h1>
            <p class="text-gray-600">Manajemen kategori sarana prasarana</p>
        </div>
        <a href="create.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
            <i class="fas fa-plus mr-2"></i>Tambah Kategori
        </a>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Sarpras</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($kategori)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3 block"></i>
                                Tidak ada data kategori
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($kategori as $i => $k): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-500"><?= $i + 1 ?></td>
                                <td class="px-6 py-4">
                                    <span class="font-medium text-gray-800"><?= e($k['nama']) ?></span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= e($k['deskripsi'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?= $k['total_sarpras'] ?> item
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="edit.php?id=<?= $k['id'] ?>"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($k['total_sarpras'] == 0): ?>
                                            <a href="delete.php?id=<?= $k['id'] ?>"
                                                onclick="return confirm('Yakin ingin menghapus kategori ini?')"
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="p-2 text-gray-400 cursor-not-allowed" title="Tidak dapat dihapus (memiliki sarpras)">
                                                <i class="fas fa-trash"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>