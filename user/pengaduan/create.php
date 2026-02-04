<?php

/**
 * User - Create Pengaduan
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Laporkan Kerusakan');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['user']);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $judul = sanitize($_POST['judul'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $lokasi = sanitize($_POST['lokasi'] ?? '');
        $jenis_sarpras = sanitize($_POST['jenis_sarpras'] ?? '');

        if (empty($judul) || empty($deskripsi)) {
            $error = 'Judul dan deskripsi harus diisi.';
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
                    "INSERT INTO pengaduan (user_id, judul, deskripsi, lokasi, jenis_sarpras, foto, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')",
                    [getCurrentUser()['id'], $judul, $deskripsi, $lokasi, $jenis_sarpras, $foto]
                );

                logActivity('CREATE_PENGADUAN', "Created pengaduan: $judul");
                setFlash('success', 'Pengaduan berhasil dikirim. Kami akan segera menindaklanjuti.');
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
            <h1 class="text-2xl font-bold text-gray-800">Laporkan Kerusakan</h1>
            <p class="text-gray-600">Laporkan sarpras yang rusak atau bermasalah</p>
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
        <form method="POST" action="" enctype="multipart/form-data">
            <?= csrfField() ?>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Judul Laporan *</label>
                    <input type="text" name="judul" value="<?= e($_POST['judul'] ?? '') ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        placeholder="Contoh: AC Ruang Lab Komputer Tidak Berfungsi"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Masalah *</label>
                    <textarea name="deskripsi" rows="4" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        placeholder="Jelaskan masalah secara detail..."><?= e($_POST['deskripsi'] ?? '') ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                        <input type="text" name="lokasi" value="<?= e($_POST['lokasi'] ?? '') ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            placeholder="Contoh: Lab Komputer 1, Lantai 2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Sarpras</label>
                        <input type="text" name="jenis_sarpras" value="<?= e($_POST['jenis_sarpras'] ?? '') ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            placeholder="Contoh: AC, Komputer, Proyektor">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Foto (Opsional)</label>
                    <input type="file" name="foto" accept="image/*"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <p class="text-xs text-gray-500 mt-1">Upload foto kerusakan untuk mempercepat penanganan</p>
                </div>
            </div>

            <div class="mt-8 flex gap-4">
                <button type="submit" class="flex-1 py-3 px-4 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-paper-plane mr-2"></i>Kirim Laporan
                </button>
                <a href="index.php" class="flex-1 py-3 px-4 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>