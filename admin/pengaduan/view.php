<?php

/**
 * Admin - View & Process Pengaduan
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Detail Pengaduan');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin', 'petugas']);

$id = intval($_GET['id'] ?? 0);
$pengaduan = fetch("
    SELECT pg.*, u.nama_lengkap, u.email, u.phone
    FROM pengaduan pg 
    JOIN users u ON pg.user_id = u.id 
    WHERE pg.id = ?
", [$id]);

if (!$pengaduan) {
    setFlash('error', 'Pengaduan tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_status') {
            $newStatus = sanitize($_POST['status'] ?? '');
            if (in_array($newStatus, ['pending', 'proses', 'selesai', 'ditutup'])) {
                query("UPDATE pengaduan SET status = ? WHERE id = ?", [$newStatus, $id]);
                logActivity('UPDATE_PENGADUAN', "Updated pengaduan #$id status to $newStatus");
                setFlash('success', 'Status pengaduan berhasil diperbarui.');
                header('Location: view.php?id=' . $id);
                exit;
            }
        } elseif ($action === 'add_note') {
            $catatan = sanitize($_POST['catatan'] ?? '');
            if (!empty($catatan)) {
                query(
                    "INSERT INTO catatan_pengaduan (pengaduan_id, user_id, catatan) VALUES (?, ?, ?)",
                    [$id, getCurrentUser()['id'], $catatan]
                );
                logActivity('ADD_CATATAN_PENGADUAN', "Added note to pengaduan #$id");
                setFlash('success', 'Catatan berhasil ditambahkan.');
                header('Location: view.php?id=' . $id);
                exit;
            } else {
                $error = 'Catatan tidak boleh kosong.';
            }
        }
    }
}

// Get catatan
$catatan = fetchAll("
    SELECT c.*, u.nama_lengkap, u.role
    FROM catatan_pengaduan c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.pengaduan_id = ? 
    ORDER BY c.created_at ASC
", [$id]);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Detail Pengaduan</h1>
            <p class="text-gray-600">#<?= $id ?></p>
        </div>
        <a href="index.php" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i><?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Pengaduan Info -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-start justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800"><?= e($pengaduan['judul']) ?></h2>
                    <?= getStatusBadge($pengaduan['status']) ?>
                </div>

                <p class="text-gray-700 mb-6"><?= nl2br(e($pengaduan['deskripsi'])) ?></p>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Lokasi</p>
                        <p class="font-medium"><?= e($pengaduan['lokasi'] ?? '-') ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Jenis Sarpras</p>
                        <p class="font-medium"><?= e($pengaduan['jenis_sarpras'] ?? '-') ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Pelapor</p>
                        <p class="font-medium"><?= e($pengaduan['nama_lengkap']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Tanggal Lapor</p>
                        <p class="font-medium"><?= formatDateTime($pengaduan['created_at']) ?></p>
                    </div>
                </div>

                <?php if ($pengaduan['foto']): ?>
                    <div class="mt-4">
                        <p class="text-gray-500 mb-2">Foto</p>
                        <img src="<?= url($pengaduan['foto']) ?>" alt="Foto pengaduan" class="max-w-md rounded-lg">
                    </div>
                <?php endif; ?>
            </div>

            <!-- Catatan / Timeline -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Catatan & Tindak Lanjut</h3>

                <?php if (empty($catatan)): ?>
                    <p class="text-gray-500 text-center py-4">Belum ada catatan</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($catatan as $c): ?>
                            <div class="border-l-4 border-blue-500 pl-4 py-2">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-medium text-gray-800"><?= e($c['nama_lengkap']) ?></span>
                                    <span class="text-xs text-gray-500"><?= formatDateTime($c['created_at']) ?></span>
                                </div>
                                <p class="text-gray-700"><?= nl2br(e($c['catatan'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Add Note Form -->
                <form method="POST" action="" class="mt-6 pt-4 border-t">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="add_note">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tambah Catatan</label>
                    <textarea name="catatan" rows="3" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Tulis catatan tindak lanjut..."></textarea>
                    <button type="submit" class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-2"></i>Tambah Catatan
                    </button>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Update Status -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Ubah Status</h3>
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_status">
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 mb-4">
                        <option value="pending" <?= $pengaduan['status'] === 'pending' ? 'selected' : '' ?>>Menunggu</option>
                        <option value="proses" <?= $pengaduan['status'] === 'proses' ? 'selected' : '' ?>>Diproses</option>
                        <option value="selesai" <?= $pengaduan['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="ditutup" <?= $pengaduan['status'] === 'ditutup' ? 'selected' : '' ?>>Ditutup</option>
                    </select>
                    <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-save mr-2"></i>Simpan Status
                    </button>
                </form>
            </div>

            <!-- Pelapor Info -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Info Pelapor</h3>
                <div class="space-y-2 text-sm">
                    <div>
                        <p class="text-gray-500">Nama</p>
                        <p class="font-medium"><?= e($pengaduan['nama_lengkap']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Email</p>
                        <p class="font-medium"><?= e($pengaduan['email'] ?? '-') ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Telepon</p>
                        <p class="font-medium"><?= e($pengaduan['phone'] ?? '-') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>