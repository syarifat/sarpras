<?php

/**
 * Admin - Pre-borrow Inspection
 * Sarpras Management System - Tier 2
 */

define('PAGE_TITLE', 'Inspeksi Serah Terima');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin', 'petugas']);

$id = intval($_GET['id'] ?? 0);
$peminjaman = fetch("
    SELECT p.*, u.nama_lengkap, u.phone, s.kode as sarpras_kode, s.nama as sarpras_nama, s.kondisi as kondisi_awal
    FROM peminjaman p
    JOIN users u ON p.user_id = u.id
    JOIN sarpras s ON p.sarpras_id = s.id
    WHERE p.id = ? AND p.status = 'approved'
", [$id]);

if (!$peminjaman) {
    setFlash('error', 'Peminjaman tidak ditemukan atau belum disetujui.');
    header('Location: index.php');
    exit;
}

// Check if already inspected
$existingInspection = fetch("SELECT * FROM inspection_checklist WHERE peminjaman_id = ? AND tipe = 'serah_terima'", [$id]);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $kondisi_fisik = sanitize($_POST['kondisi_fisik'] ?? '');
        $kelengkapan = sanitize($_POST['kelengkapan'] ?? '');
        $fungsi = sanitize($_POST['fungsi'] ?? '');
        $catatan = sanitize($_POST['catatan'] ?? '');

        if (!$kondisi_fisik || !$kelengkapan || !$fungsi) {
            $error = 'Semua field checklist harus diisi.';
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
                // Save inspection
                query(
                    "INSERT INTO inspection_checklist (peminjaman_id, tipe, kondisi_fisik, kelengkapan, fungsi, catatan, foto, inspected_by) VALUES (?, 'serah_terima', ?, ?, ?, ?, ?, ?)",
                    [$id, $kondisi_fisik, $kelengkapan, $fungsi, $catatan, $foto, getCurrentUser()['id']]
                );

                // Update peminjaman status to active
                query("UPDATE peminjaman SET status = 'active' WHERE id = ?", [$id]);

                // Log activity
                logActivity('SERAH_TERIMA', "Completed handover inspection for: {$peminjaman['kode_peminjaman']}");

                setFlash('success', 'Inspeksi serah terima berhasil. Peminjaman sekarang aktif.');
                header('Location: view.php?id=' . $id);
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
            <h1 class="text-2xl font-bold text-gray-800">Inspeksi Serah Terima</h1>
            <p class="text-gray-600">Checklist kondisi sebelum penyerahan ke peminjam</p>
        </div>
        <a href="view.php?id=<?= $id ?>" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <!-- Peminjaman Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Kode Peminjaman</p>
                <p class="font-semibold text-gray-800"><?= e($peminjaman['kode_peminjaman']) ?></p>
            </div>
            <div>
                <p class="text-gray-500">Peminjam</p>
                <p class="font-semibold text-gray-800"><?= e($peminjaman['nama_lengkap']) ?></p>
            </div>
            <div>
                <p class="text-gray-500">Sarpras</p>
                <p class="font-semibold text-gray-800"><?= e($peminjaman['sarpras_nama']) ?></p>
            </div>
            <div>
                <p class="text-gray-500">Kondisi Tercatat</p>
                <p class="font-semibold"><?= getConditionBadge($peminjaman['kondisi_awal']) ?></p>
            </div>
        </div>
    </div>

    <?php if ($existingInspection): ?>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <p class="text-green-700">
                <i class="fas fa-check-circle mr-2"></i>
                Inspeksi serah terima sudah dilakukan pada <?= formatDateTime($existingInspection['created_at']) ?>
            </p>
        </div>
    <?php else: ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i><?= e($error) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="POST" action="" enctype="multipart/form-data">
                <?= csrfField() ?>

                <div class="space-y-6">
                    <!-- Kondisi Fisik -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            <i class="fas fa-box mr-2"></i>Kondisi Fisik *
                        </label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="kondisi_fisik" value="baik" class="peer sr-only" required>
                                <div class="p-4 border-2 rounded-lg text-center peer-checked:border-green-500 peer-checked:bg-green-50 hover:bg-gray-50 transition">
                                    <i class="fas fa-check-circle text-2xl text-green-500 mb-2"></i>
                                    <p class="font-medium">Baik</p>
                                    <p class="text-xs text-gray-500">Tidak ada cacat</p>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="kondisi_fisik" value="cacat_minor" class="peer sr-only">
                                <div class="p-4 border-2 rounded-lg text-center peer-checked:border-yellow-500 peer-checked:bg-yellow-50 hover:bg-gray-50 transition">
                                    <i class="fas fa-exclamation-circle text-2xl text-yellow-500 mb-2"></i>
                                    <p class="font-medium">Cacat Minor</p>
                                    <p class="text-xs text-gray-500">Goresan/lecet kecil</p>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="kondisi_fisik" value="rusak" class="peer sr-only">
                                <div class="p-4 border-2 rounded-lg text-center peer-checked:border-red-500 peer-checked:bg-red-50 hover:bg-gray-50 transition">
                                    <i class="fas fa-times-circle text-2xl text-red-500 mb-2"></i>
                                    <p class="font-medium">Rusak</p>
                                    <p class="text-xs text-gray-500">Kerusakan terlihat</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Kelengkapan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            <i class="fas fa-list-check mr-2"></i>Kelengkapan *
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="kelengkapan" value="lengkap" class="peer sr-only" required>
                                <div class="p-4 border-2 rounded-lg text-center peer-checked:border-green-500 peer-checked:bg-green-50 hover:bg-gray-50 transition">
                                    <i class="fas fa-clipboard-check text-2xl text-green-500 mb-2"></i>
                                    <p class="font-medium">Lengkap</p>
                                    <p class="text-xs text-gray-500">Semua komponen ada</p>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="kelengkapan" value="tidak_lengkap" class="peer sr-only">
                                <div class="p-4 border-2 rounded-lg text-center peer-checked:border-red-500 peer-checked:bg-red-50 hover:bg-gray-50 transition">
                                    <i class="fas fa-clipboard-list text-2xl text-red-500 mb-2"></i>
                                    <p class="font-medium">Tidak Lengkap</p>
                                    <p class="text-xs text-gray-500">Ada yang kurang</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Fungsi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            <i class="fas fa-cog mr-2"></i>Fungsi *
                        </label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="fungsi" value="normal" class="peer sr-only" required>
                                <div class="p-4 border-2 rounded-lg text-center peer-checked:border-green-500 peer-checked:bg-green-50 hover:bg-gray-50 transition">
                                    <i class="fas fa-play-circle text-2xl text-green-500 mb-2"></i>
                                    <p class="font-medium">Normal</p>
                                    <p class="text-xs text-gray-500">Berfungsi baik</p>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="fungsi" value="terganggu" class="peer sr-only">
                                <div class="p-4 border-2 rounded-lg text-center peer-checked:border-yellow-500 peer-checked:bg-yellow-50 hover:bg-gray-50 transition">
                                    <i class="fas fa-pause-circle text-2xl text-yellow-500 mb-2"></i>
                                    <p class="font-medium">Terganggu</p>
                                    <p class="text-xs text-gray-500">Ada gangguan</p>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="fungsi" value="tidak_fungsi" class="peer sr-only">
                                <div class="p-4 border-2 rounded-lg text-center peer-checked:border-red-500 peer-checked:bg-red-50 hover:bg-gray-50 transition">
                                    <i class="fas fa-stop-circle text-2xl text-red-500 mb-2"></i>
                                    <p class="font-medium">Tidak Berfungsi</p>
                                    <p class="text-xs text-gray-500">Rusak total</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Tambahan</label>
                        <textarea name="catatan" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Catatan detail jika ada..."><?= e($_POST['catatan'] ?? '') ?></textarea>
                    </div>

                    <!-- Foto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Foto Dokumentasi (Opsional)</label>
                        <input type="file" name="foto" accept="image/*"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Ambil foto kondisi sarpras saat serah terima</p>
                    </div>
                </div>

                <div class="mt-8">
                    <button type="submit" class="w-full py-3 px-4 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-check-double mr-2"></i>Selesaikan Inspeksi & Aktifkan Peminjaman
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>