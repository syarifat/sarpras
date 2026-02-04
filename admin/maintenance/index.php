<?php

/**
 * Admin - Maintenance Schedule List
 * Sarpras Management System - Tier 2
 */

define('PAGE_TITLE', 'Jadwal Maintenance');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin', 'petugas']);

// Filters
$search = sanitize($_GET['search'] ?? '');
$status = sanitize($_GET['status'] ?? '');

// Build query
$where = ["1=1"];
$params = [];

if ($search) {
    $where[] = "(s.nama LIKE ? OR s.kode LIKE ? OR ms.deskripsi LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $where[] = "ms.status = ?";
    $params[] = $status;
}

$whereClause = implode(' AND ', $where);

// Get schedules
$schedules = fetchAll("
    SELECT ms.*, s.kode as sarpras_kode, s.nama as sarpras_nama, k.nama as kategori_nama,
           u.nama_lengkap as created_by_name,
           DATEDIFF(ms.tanggal_berikutnya, CURDATE()) as days_until
    FROM maintenance_schedule ms
    JOIN sarpras s ON ms.sarpras_id = s.id
    LEFT JOIN kategori_sarpras k ON s.kategori_id = k.id
    LEFT JOIN users u ON ms.created_by = u.id
    WHERE $whereClause
    ORDER BY ms.tanggal_berikutnya ASC
", $params);

// Get upcoming maintenance (due within 7 days)
$upcoming = fetchAll("
    SELECT ms.*, s.kode, s.nama as sarpras_nama
    FROM maintenance_schedule ms
    JOIN sarpras s ON ms.sarpras_id = s.id
    WHERE ms.status = 'aktif' AND ms.tanggal_berikutnya <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY ms.tanggal_berikutnya ASC
");

// Get recent maintenance log
$recentLogs = fetchAll("
    SELECT ml.*, s.nama as sarpras_nama, u.nama_lengkap as performed_by_name
    FROM maintenance_log ml
    JOIN sarpras s ON ml.sarpras_id = s.id
    LEFT JOIN users u ON ml.performed_by = u.id
    ORDER BY ml.tgl_maintenance DESC
    LIMIT 10
");

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Jadwal Maintenance</h1>
            <p class="text-gray-600">Kelola jadwal perawatan preventif sarpras</p>
        </div>
        <div class="flex gap-2">
            <a href="create.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-plus mr-2"></i>Tambah Jadwal
            </a>
            <a href="log.php" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-clipboard-check mr-2"></i>Catat Maintenance
            </a>
        </div>
    </div>

    <?php if (!empty($upcoming)): ?>
        <!-- Upcoming Maintenance Alert -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
            <div class="flex items-center mb-2">
                <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                <h3 class="font-semibold text-yellow-800">Maintenance Mendatang (7 Hari)</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                <?php foreach ($upcoming as $u): ?>
                    <div class="bg-white rounded px-3 py-2 flex items-center justify-between">
                        <span class="text-sm"><?= e($u['sarpras_nama']) ?></span>
                        <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">
                            <?= formatDate($u['tanggal_berikutnya'], 'd M Y') ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Nama sarpras, kode..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="nonaktif" <?= $status === 'nonaktif' ? 'selected' : '' ?>>Non-aktif</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-search mr-2"></i>Filter
            </button>
            <a href="index.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Reset</a>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Schedule List -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b">
                <h2 class="font-semibold text-gray-800">Daftar Jadwal</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sarpras</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Frekuensi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Berikutnya</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if (empty($schedules)): ?>
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    <i class="fas fa-calendar-times text-4xl mb-3 block"></i>
                                    Belum ada jadwal maintenance
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($schedules as $s):
                                $isOverdue = $s['days_until'] < 0;
                                $isDueSoon = $s['days_until'] >= 0 && $s['days_until'] <= 7;
                            ?>
                                <tr class="hover:bg-gray-50 <?= $isOverdue ? 'bg-red-50' : ($isDueSoon ? 'bg-yellow-50' : '') ?>">
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-800"><?= e($s['sarpras_nama']) ?></p>
                                        <p class="text-xs text-gray-500"><?= e($s['sarpras_kode']) ?></p>
                                    </td>
                                    <td class="px-4 py-3 text-sm"><?= ucfirst(str_replace('_', ' ', $s['jenis_maintenance'])) ?></td>
                                    <td class="px-4 py-3 text-sm"><?= ucfirst($s['frekuensi']) ?></td>
                                    <td class="px-4 py-3">
                                        <p class="text-sm"><?= formatDate($s['tanggal_berikutnya'], 'd M Y') ?></p>
                                        <?php if ($isOverdue): ?>
                                            <span class="text-xs text-red-600 font-semibold">Terlambat <?= abs($s['days_until']) ?> hari</span>
                                        <?php elseif ($isDueSoon): ?>
                                            <span class="text-xs text-yellow-600"><?= $s['days_until'] ?> hari lagi</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if ($s['status'] === 'aktif'): ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">Non-aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="log.php?schedule_id=<?= $s['id'] ?>" class="text-green-600 hover:underline text-sm mr-2" title="Catat">
                                            <i class="fas fa-clipboard-check"></i>
                                        </a>
                                        <a href="edit.php?id=<?= $s['id'] ?>" class="text-blue-600 hover:underline text-sm mr-2" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $s['id'] ?>" class="text-red-600 hover:underline text-sm" title="Hapus"
                                            onclick="return confirm('Yakin hapus jadwal ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Maintenance Log -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">Log Terbaru</h2>
                <a href="history.php" class="text-sm text-blue-600 hover:underline">Semua</a>
            </div>
            <div class="divide-y">
                <?php if (empty($recentLogs)): ?>
                    <p class="p-4 text-gray-500 text-center text-sm">Belum ada log</p>
                <?php else: ?>
                    <?php foreach ($recentLogs as $log): ?>
                        <div class="p-3 hover:bg-gray-50">
                            <p class="font-medium text-gray-800 text-sm"><?= e($log['sarpras_nama']) ?></p>
                            <p class="text-xs text-gray-500"><?= e($log['jenis_maintenance']) ?> - <?= formatDate($log['tgl_maintenance']) ?></p>
                            <div class="mt-1">
                                <?php if ($log['hasil'] === 'berhasil'): ?>
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">Berhasil</span>
                                <?php elseif ($log['hasil'] === 'perlu_perbaikan'): ?>
                                    <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded">Perlu Perbaikan</span>
                                <?php else: ?>
                                    <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded">Gagal</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>