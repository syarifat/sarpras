<?php

/**
 * Admin - Process Pengembalian
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Proses Pengembalian');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin', 'petugas']);

$kode = sanitize($_GET['kode'] ?? $_POST['kode'] ?? '');
$peminjaman = null;
$error = '';
$success = '';

// Find peminjaman by code
if ($kode) {
    $peminjaman = fetch("
        SELECT p.*, u.nama_lengkap, u.email, u.phone,
               s.nama as sarpras_nama, s.kode as sarpras_kode, s.id as sarpras_id, s.kondisi as sarpras_kondisi
        FROM peminjaman p 
        JOIN users u ON p.user_id = u.id 
        JOIN sarpras s ON p.sarpras_id = s.id 
        WHERE p.kode_peminjaman = ? AND p.status IN ('approved', 'active')
    ", [$kode]);

    if (!$peminjaman) {
        $error = 'Peminjaman tidak ditemukan atau sudah dikembalikan.';
    }
}

// Process return
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } elseif (!$peminjaman) {
        $error = 'Peminjaman tidak valid.';
    } else {
        $tgl_pengembalian = sanitize($_POST['tgl_pengembalian'] ?? date('Y-m-d'));
        $kondisi_alat = sanitize($_POST['kondisi_alat'] ?? 'baik');
        $deskripsi_kerusakan = sanitize($_POST['deskripsi_kerusakan'] ?? '');
        $catatan_petugas = sanitize($_POST['catatan_petugas'] ?? '');

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
            // Insert pengembalian record
            query(
                "INSERT INTO pengembalian (peminjaman_id, tgl_pengembalian, kondisi_alat, deskripsi_kerusakan, foto_pengembalian, catatan_petugas, processed_by) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$peminjaman['id'], $tgl_pengembalian, $kondisi_alat, $deskripsi_kerusakan, $foto, $catatan_petugas, getCurrentUser()['id']]
            );

            // Update peminjaman status
            query(
                "UPDATE peminjaman SET status = 'returned', tgl_kembali_aktual = ? WHERE id = ?",
                [$tgl_pengembalian, $peminjaman['id']]
            );

            // Increase stock
            query(
                "UPDATE sarpras SET jumlah_stok = jumlah_stok + ? WHERE id = ?",
                [$peminjaman['jumlah'], $peminjaman['sarpras_id']]
            );

            // Update sarpras condition if damaged
            if ($kondisi_alat !== 'baik' && $kondisi_alat !== $peminjaman['sarpras_kondisi']) {
                query(
                    "UPDATE sarpras SET kondisi = ? WHERE id = ?",
                    [$kondisi_alat === 'hilang' ? 'rusak_berat' : $kondisi_alat, $peminjaman['sarpras_id']]
                );
            }

            logActivity('PENGEMBALIAN', "Processed return: {$peminjaman['kode_peminjaman']} - Kondisi: $kondisi_alat");
            setFlash('success', 'Pengembalian berhasil diproses.');
            header('Location: index.php');
            exit;
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Proses Pengembalian</h1>
            <p class="text-gray-600">Scan QR atau input kode peminjaman</p>
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

    <!-- Search Form -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <form method="GET" class="flex gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Kode Peminjaman</label>
                <input type="text" name="kode" value="<?= e($kode) ?>"
                    placeholder="Masukkan atau scan kode peminjaman..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-mono"
                    autofocus>
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
            </div>
        </form>
    </div>

    <?php if ($peminjaman): ?>
        <!-- Peminjaman Info -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Detail Peminjaman</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Kode Peminjaman</p>
                    <p class="font-medium text-gray-800 font-mono text-lg"><?= e($peminjaman['kode_peminjaman']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Peminjam</p>
                    <p class="font-medium text-gray-800"><?= e($peminjaman['nama_lengkap']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Sarpras</p>
                    <p class="font-medium text-gray-800"><?= e($peminjaman['sarpras_nama']) ?></p>
                    <p class="text-xs text-gray-500"><?= e($peminjaman['sarpras_kode']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Jumlah</p>
                    <p class="font-medium text-gray-800"><?= $peminjaman['jumlah'] ?> unit</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tanggal Pinjam</p>
                    <p class="font-medium text-gray-800"><?= formatDate($peminjaman['tgl_pinjam'], 'd F Y') ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Jatuh Tempo</p>
                    <?php $isOverdue = strtotime($peminjaman['tgl_kembali_rencana']) < strtotime('today'); ?>
                    <p class="font-medium <?= $isOverdue ? 'text-red-600' : 'text-gray-800' ?>">
                        <?= formatDate($peminjaman['tgl_kembali_rencana'], 'd F Y') ?>
                        <?php if ($isOverdue): ?>
                            <span class="text-xs bg-red-100 px-2 py-1 rounded ml-1">TERLAMBAT</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Process Form -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Inspeksi & Pencatatan Kondisi</h2>

            <form method="POST" action="" enctype="multipart/form-data">
                <?= csrfField() ?>
                <input type="hidden" name="kode" value="<?= e($kode) ?>">
                <input type="hidden" name="process" value="1">

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Pengembalian *</label>
                        <input type="date" name="tgl_pengembalian" value="<?= date('Y-m-d') ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kondisi Alat Saat Dikembalikan *</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-green-50 has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                                <input type="radio" name="kondisi_alat" value="baik" checked class="hidden">
                                <div class="text-center w-full">
                                    <i class="fas fa-check-circle text-2xl text-green-500 mb-1"></i>
                                    <p class="font-medium text-green-700">Baik</p>
                                    <p class="text-xs text-gray-500">Tidak ada kerusakan</p>
                                </div>
                            </label>
                            <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-yellow-50 has-[:checked]:border-yellow-500 has-[:checked]:bg-yellow-50">
                                <input type="radio" name="kondisi_alat" value="rusak_ringan" class="hidden">
                                <div class="text-center w-full">
                                    <i class="fas fa-exclamation-triangle text-2xl text-yellow-500 mb-1"></i>
                                    <p class="font-medium text-yellow-700">Rusak Ringan</p>
                                    <p class="text-xs text-gray-500">Masih bisa dipakai</p>
                                </div>
                            </label>
                            <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-red-50 has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                                <input type="radio" name="kondisi_alat" value="rusak_berat" class="hidden">
                                <div class="text-center w-full">
                                    <i class="fas fa-times-circle text-2xl text-red-500 mb-1"></i>
                                    <p class="font-medium text-red-700">Rusak Berat</p>
                                    <p class="text-xs text-gray-500">Perlu perbaikan</p>
                                </div>
                            </label>
                            <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-100 has-[:checked]:border-gray-800 has-[:checked]:bg-gray-100">
                                <input type="radio" name="kondisi_alat" value="hilang" class="hidden">
                                <div class="text-center w-full">
                                    <i class="fas fa-question-circle text-2xl text-gray-700 mb-1"></i>
                                    <p class="font-medium text-gray-700">Hilang</p>
                                    <p class="text-xs text-gray-500">Tidak dikembalikan</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Kerusakan</label>
                        <textarea name="deskripsi_kerusakan" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Jelaskan kerusakan yang ditemukan (jika ada)..."><?= e($_POST['deskripsi_kerusakan'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Foto Kondisi Alat</label>
                        <input type="file" name="foto" accept="image/*"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Upload foto untuk dokumentasi kondisi alat</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Petugas</label>
                        <textarea name="catatan_petugas" rows="2"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Catatan tambahan..."><?= e($_POST['catatan_petugas'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="mt-8">
                    <button type="submit" class="w-full py-3 px-4 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition text-lg"
                        onclick="return confirm('Proses pengembalian ini?')">
                        <i class="fas fa-check mr-2"></i>Proses Pengembalian
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
    // Add visual feedback for radio button selection
    document.querySelectorAll('input[name="kondisi_alat"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('input[name="kondisi_alat"]').forEach(r => {
                r.closest('label').classList.remove('border-green-500', 'border-yellow-500', 'border-red-500', 'border-gray-800');
            });
        });
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>