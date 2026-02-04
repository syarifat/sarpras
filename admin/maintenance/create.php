<?php

/**
 * Admin - Create Maintenance Schedule
 * Sarpras Management System - Tier 2
 */

define('PAGE_TITLE', 'Tambah Jadwal Maintenance');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin', 'petugas']);

// Get sarpras for dropdown
$sarpras = fetchAll("SELECT id, kode, nama FROM sarpras ORDER BY nama");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $sarpras_id = intval($_POST['sarpras_id'] ?? 0);
        $jenis = sanitize($_POST['jenis_maintenance'] ?? '');
        $frekuensi = sanitize($_POST['frekuensi'] ?? '');
        $tanggal_berikutnya = sanitize($_POST['tanggal_berikutnya'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');

        if (!$sarpras_id || !$jenis || !$frekuensi || !$tanggal_berikutnya) {
            $error = 'Semua field wajib harus diisi.';
        } else {
            query(
                "INSERT INTO maintenance_schedule (sarpras_id, jenis_maintenance, frekuensi, tanggal_berikutnya, deskripsi, created_by) VALUES (?, ?, ?, ?, ?, ?)",
                [$sarpras_id, $jenis, $frekuensi, $tanggal_berikutnya, $deskripsi, getCurrentUser()['id']]
            );

            logActivity('CREATE_MAINTENANCE_SCHEDULE', "Created maintenance schedule for sarpras ID: $sarpras_id");
            setFlash('success', 'Jadwal maintenance berhasil ditambahkan.');
            header('Location: index.php');
            exit;
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tambah Jadwal Maintenance</h1>
            <p class="text-gray-600">Buat jadwal perawatan preventif sarpras</p>
        </div>
        <a href="index.php" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i><?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="">
            <?= csrfField() ?>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sarpras *</label>
                    <select name="sarpras_id" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Sarpras</option>
                        <?php foreach ($sarpras as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= ($_POST['sarpras_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                [<?= e($s['kode']) ?>] <?= e($s['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Maintenance *</label>
                        <select name="jenis_maintenance" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Jenis</option>
                            <option value="preventif" <?= ($_POST['jenis_maintenance'] ?? '') === 'preventif' ? 'selected' : '' ?>>Preventif</option>
                            <option value="kalibrasi" <?= ($_POST['jenis_maintenance'] ?? '') === 'kalibrasi' ? 'selected' : '' ?>>Kalibrasi</option>
                            <option value="pembersihan" <?= ($_POST['jenis_maintenance'] ?? '') === 'pembersihan' ? 'selected' : '' ?>>Pembersihan</option>
                            <option value="penggantian_komponen" <?= ($_POST['jenis_maintenance'] ?? '') === 'penggantian_komponen' ? 'selected' : '' ?>>Penggantian Komponen</option>
                            <option value="lainnya" <?= ($_POST['jenis_maintenance'] ?? '') === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Frekuensi *</label>
                        <select name="frekuensi" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Frekuensi</option>
                            <option value="harian" <?= ($_POST['frekuensi'] ?? '') === 'harian' ? 'selected' : '' ?>>Harian</option>
                            <option value="mingguan" <?= ($_POST['frekuensi'] ?? '') === 'mingguan' ? 'selected' : '' ?>>Mingguan</option>
                            <option value="bulanan" <?= ($_POST['frekuensi'] ?? '') === 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                            <option value="triwulan" <?= ($_POST['frekuensi'] ?? '') === 'triwulan' ? 'selected' : '' ?>>Triwulan</option>
                            <option value="tahunan" <?= ($_POST['frekuensi'] ?? '') === 'tahunan' ? 'selected' : '' ?>>Tahunan</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Maintenance Berikutnya *</label>
                    <input type="date" name="tanggal_berikutnya" value="<?= e($_POST['tanggal_berikutnya'] ?? date('Y-m-d')) ?>"
                        min="<?= date('Y-m-d') ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi / Catatan</label>
                    <textarea name="deskripsi" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="Detail maintenance yang perlu dilakukan..."><?= e($_POST['deskripsi'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="mt-8 flex gap-4">
                <button type="submit" class="flex-1 py-3 px-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-save mr-2"></i>Simpan Jadwal
                </button>
                <a href="index.php" class="flex-1 py-3 px-4 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>