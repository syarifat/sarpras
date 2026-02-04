<?php

/**
 * User - Peminjaman List
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Peminjaman Saya');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['user']);

$userId = getCurrentUser()['id'];

// Get all user peminjaman
$peminjaman = fetchAll("
    SELECT p.*, s.nama as sarpras_nama, s.kode as sarpras_kode,
           a.nama_lengkap as approved_by_name
    FROM peminjaman p 
    JOIN sarpras s ON p.sarpras_id = s.id 
    LEFT JOIN users a ON p.approved_by = a.id
    WHERE p.user_id = ?
    ORDER BY 
        CASE p.status 
            WHEN 'pending' THEN 1 
            WHEN 'active' THEN 2
            WHEN 'approved' THEN 3
            ELSE 4 
        END,
        p.created_at DESC
", [$userId]);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Peminjaman Saya</h1>
            <p class="text-gray-600">Daftar peminjaman sarpras Anda</p>
        </div>
        <a href="create.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
            <i class="fas fa-plus mr-2"></i>Ajukan Peminjaman
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
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
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3 block"></i>
                                Belum ada peminjaman. <a href="create.php" class="text-blue-600 hover:underline">Ajukan sekarang</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($peminjaman as $p):
                            $isOverdue = in_array($p['status'], ['approved', 'active']) && strtotime($p['tgl_kembali_rencana']) < strtotime('today');
                        ?>
                            <tr class="hover:bg-gray-50 <?= $p['status'] === 'pending' ? 'bg-yellow-50' : ($isOverdue ? 'bg-red-50' : '') ?>">
                                <td class="px-6 py-4">
                                    <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded"><?= e($p['kode_peminjaman']) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-800"><?= e($p['sarpras_nama']) ?></p>
                                    <p class="text-xs text-gray-500"><?= e($p['sarpras_kode']) ?></p>
                                </td>
                                <td class="px-6 py-4 text-center"><?= $p['jumlah'] ?></td>
                                <td class="px-6 py-4 text-sm"><?= formatDate($p['tgl_pinjam']) ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <?= formatDate($p['tgl_kembali_rencana']) ?>
                                    <?php if ($isOverdue): ?>
                                        <span class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded-full ml-1">TERLAMBAT</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4"><?= getStatusBadge($p['status']) ?></td>
                                <td class="px-6 py-4 text-center">
                                    <a href="view.php?id=<?= $p['id'] ?>" class="text-blue-600 hover:underline text-sm">Detail</a>
                                    <?php if (in_array($p['status'], ['approved', 'active'])): ?>
                                        | <a href="receipt.php?id=<?= $p['id'] ?>" class="text-green-600 hover:underline text-sm">Cetak</a>
                                    <?php endif; ?>
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