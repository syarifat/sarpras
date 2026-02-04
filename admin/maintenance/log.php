<?php

/**
 * Admin - Log Maintenance
 * Sarpras Management System - Tier 2
 */

define('PAGE_TITLE', 'Catat Pelaksanaan Maintenance');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin', 'petugas']);

$scheduleId = intval($_GET['schedule_id'] ?? 0);
$schedule = null;

if ($scheduleId) {
    $schedule = fetch("
        SELECT ms.*, s.kode, s.nama as sarpras_nama 
        FROM maintenance_schedule ms 
        JOIN sarpras s ON ms.sarpras_id = s.id 
        WHERE ms.id = ?
    ", [$scheduleId]);
}

// Get sarpras for dropdown
$sarpras = fetchAll("SELECT id, kode, nama FROM sarpras ORDER BY nama");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $sarpras_id = intval($_POST['sarpras_id'] ?? 0);
        $schedule_id = intval($_POST['schedule_id'] ?? 0) ?: null;
        $tgl_maintenance = sanitize($_POST['tgl_maintenance'] ?? '');
        $jenis = sanitize($_POST['jenis_maintenance'] ?? '');
        $hasil = sanitize($_POST['hasil'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $catatan = sanitize($_POST['catatan'] ?? '');
        $biaya = floatval($_POST['biaya'] ?? 0);

        if (!$sarpras_id || !$tgl_maintenance || !$jenis || !$hasil) {
            $error = 'Semua field wajib harus diisi.';
        } else {
            // Handle photo upload
            $foto = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['size'] > 0) {
                $uploadResult = uploadFile($_FILES['foto']);
                if (isset($uploadResult['error'])) {
                    $error = $uploadResult['error'];
                } else {
                    $foto = $uploadResult['path'];
                }
            }

            if (!$error) {
                query(
                    "INSERT INTO maintenance_log (schedule_id, sarpras_id, tgl_maintenance, jenis_maintenance, deskripsi, biaya, hasil, catatan, foto, performed_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$schedule_id, $sarpras_id, $tgl_maintenance, $jenis, $deskripsi, $biaya, $hasil, $catatan, $foto, getCurrentUser()['id']]
                );

                // Update schedule if linked
                if ($schedule_id) {
                    // Calculate next maintenance date based on frequency
                    $scheduleData = fetch("SELECT frekuensi FROM maintenance_schedule WHERE id = ?", [$schedule_id]);
                    $intervals = [
                        'harian' => '+1 day',
                        'mingguan' => '+1 week',
                        'bulanan' => '+1 month',
                        'triwulan' => '+3 months',
                        'tahunan' => '+1 year'
                    ];
                    $interval = $intervals[$scheduleData['frekuensi']] ?? '+1 month';
                    $nextDate = date('Y-m-d', strtotime($tgl_maintenance . ' ' . $interval));

                    query(
                        "UPDATE maintenance_schedule SET tanggal_terakhir = ?, tanggal_berikutnya = ? WHERE id = ?",
                        [$tgl_maintenance, $nextDate, $schedule_id]
                    );
                }

                logActivity('LOG_MAINTENANCE', "Logged maintenance for sarpras ID: $sarpras_id");
                setFlash('success', 'Pelaksanaan maintenance berhasil dicatat.');
                header('Location: index.php');
                exit;
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Catat Pelaksanaan Maintenance</h1>
            <p class="text-gray-600">Dokumentasikan hasil maintenance yang telah dilakukan</p>
        </div>
        <a href="index.php" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <?php if ($schedule): ?>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-blue-700">
                <i class="fas fa-info-circle mr-2"></i>
                Mencatat untuk jadwal: <strong><?= e($schedule['sarpras_nama']) ?></strong> - <?= ucfirst($schedule['jenis_maintenance']) ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i><?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="schedule_id" value="<?= $scheduleId ?>">

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sarpras *</label>
                    <select name="sarpras_id" required <?= $schedule ? 'disabled' : '' ?>
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 <?= $schedule ? 'bg-gray-100' : '' ?>">
                        <option value="">Pilih Sarpras</option>
                        <?php foreach ($sarpras as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= ($schedule ? $schedule['sarpras_id'] : ($_POST['sarpras_id'] ?? '')) == $s['id'] ? 'selected' : '' ?>>
                                [<?= e($s['kode']) ?>] <?= e($s['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($schedule): ?>
                        <input type="hidden" name="sarpras_id" value="<?= $schedule['sarpras_id'] ?>">
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Pelaksanaan *</label>
                        <input type="date" name="tgl_maintenance" value="<?= e($_POST['tgl_maintenance'] ?? date('Y-m-d')) ?>"
                            max="<?= date('Y-m-d') ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Maintenance *</label>
                        <select name="jenis_maintenance" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Jenis</option>
                            <?php
                            $jenisDefault = $schedule['jenis_maintenance'] ?? ($_POST['jenis_maintenance'] ?? '');
                            $jenisOptions = ['preventif', 'kalibrasi', 'pembersihan', 'penggantian_komponen', 'perbaikan', 'lainnya'];
                            foreach ($jenisOptions as $j): ?>
                                <option value="<?= $j ?>" <?= $jenisDefault === $j ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $j)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hasil *</label>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input type="radio" name="hasil" value="berhasil" class="mr-2" <?= ($_POST['hasil'] ?? '') === 'berhasil' ? 'checked' : '' ?> required>
                            <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>Berhasil</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="hasil" value="perlu_perbaikan" class="mr-2" <?= ($_POST['hasil'] ?? '') === 'perlu_perbaikan' ? 'checked' : '' ?>>
                            <span class="text-yellow-600"><i class="fas fa-exclamation-circle mr-1"></i>Perlu Perbaikan</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="hasil" value="gagal" class="mr-2" <?= ($_POST['hasil'] ?? '') === 'gagal' ? 'checked' : '' ?>>
                            <span class="text-red-600"><i class="fas fa-times-circle mr-1"></i>Gagal</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Pekerjaan</label>
                    <textarea name="deskripsi" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="Detail pekerjaan yang dilakukan..."><?= e($_POST['deskripsi'] ?? '') ?></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Biaya (Rp)</label>
                        <input type="number" name="biaya" value="<?= e($_POST['biaya'] ?? '0') ?>" min="0" step="1000"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Foto Dokumentasi</label>
                        <input type="file" name="foto" accept="image/*"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Tambahan</label>
                    <textarea name="catatan" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="Catatan atau rekomendasi..."><?= e($_POST['catatan'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="mt-8 flex gap-4">
                <button type="submit" class="flex-1 py-3 px-4 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-clipboard-check mr-2"></i>Simpan Log
                </button>
                <a href="index.php" class="flex-1 py-3 px-4 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>