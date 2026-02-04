<?php

/**
 * Admin - Edit Sarpras
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Edit Sarpras');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin']);

$id = intval($_GET['id'] ?? 0);
$sarpras = fetch("SELECT * FROM sarpras WHERE id = ?", [$id]);

if (!$sarpras) {
    setFlash('error', 'Sarpras tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$kategoriList = fetchAll("SELECT * FROM kategori_sarpras ORDER BY nama ASC");
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $nama = sanitize($_POST['nama'] ?? '');
        $kategori_id = intval($_POST['kategori_id'] ?? 0);
        $lokasi = sanitize($_POST['lokasi'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $jumlah_stok = intval($_POST['jumlah_stok'] ?? 0);
        $kondisi = sanitize($_POST['kondisi'] ?? 'baik');

        if (empty($nama) || $kategori_id <= 0) {
            $error = 'Nama dan kategori harus diisi.';
        } elseif ($jumlah_stok < 0) {
            $error = 'Jumlah stok tidak boleh negatif.';
        } else {
            $foto = $sarpras['foto'];

            // Handle new file upload
            if (isset($_FILES['foto']) && $_FILES['foto']['size'] > 0) {
                $uploadResult = uploadFile($_FILES['foto']);
                if (isset($uploadResult['error'])) {
                    $error = $uploadResult['error'];
                } else {
                    // Delete old photo
                    if ($sarpras['foto']) {
                        deleteFile($sarpras['foto']);
                    }
                    $foto = $uploadResult['path'];
                }
            }

            if (!$error) {
                query(
                    "UPDATE sarpras SET nama = ?, kategori_id = ?, lokasi = ?, deskripsi = ?, jumlah_stok = ?, kondisi = ?, foto = ? WHERE id = ?",
                    [$nama, $kategori_id, $lokasi, $deskripsi, $jumlah_stok, $kondisi, $foto, $id]
                );

                logActivity('UPDATE_SARPRAS', "Updated sarpras: {$sarpras['kode']} - $nama");
                setFlash('success', 'Sarpras berhasil diperbarui.');
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
            <h1 class="text-2xl font-bold text-gray-800">Edit Sarpras</h1>
            <p class="text-gray-600">Edit: <?= e($sarpras['kode']) ?></p>
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kode Sarpras</label>
                        <input type="text" value="<?= e($sarpras['kode']) ?>"
                            class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg" disabled>
                        <p class="text-xs text-gray-500 mt-1">Kode tidak dapat diubah</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
                        <select name="kategori_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($kategoriList as $kat): ?>
                                <option value="<?= $kat['id'] ?>" <?= ($_POST['kategori_id'] ?? $sarpras['kategori_id']) == $kat['id'] ? 'selected' : '' ?>><?= e($kat['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Sarpras *</label>
                    <input type="text" name="nama" value="<?= e($_POST['nama'] ?? $sarpras['nama']) ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                    <input type="text" name="lokasi" value="<?= e($_POST['lokasi'] ?? $sarpras['lokasi']) ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="deskripsi" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= e($_POST['deskripsi'] ?? $sarpras['deskripsi']) ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Stok *</label>
                        <input type="number" name="jumlah_stok" value="<?= e($_POST['jumlah_stok'] ?? $sarpras['jumlah_stok']) ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required min="0">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kondisi *</label>
                        <select name="kondisi" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <?php $kondisi = $_POST['kondisi'] ?? $sarpras['kondisi']; ?>
                            <option value="baik" <?= $kondisi === 'baik' ? 'selected' : '' ?>>Baik</option>
                            <option value="rusak_ringan" <?= $kondisi === 'rusak_ringan' ? 'selected' : '' ?>>Rusak Ringan</option>
                            <option value="rusak_berat" <?= $kondisi === 'rusak_berat' ? 'selected' : '' ?>>Rusak Berat</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Foto</label>
                    <?php if ($sarpras['foto']): ?>
                        <div class="mb-3">
                            <img src="/sarpras_lagi/<?= e($sarpras['foto']) ?>" alt="Foto sarpras" class="w-32 h-32 object-cover rounded-lg">
                            <p class="text-xs text-gray-500 mt-1">Foto saat ini</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="foto" accept="image/*"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Biarkan kosong jika tidak ingin mengubah foto</p>
                </div>
            </div>

            <div class="mt-8 flex gap-4">
                <button type="submit" class="flex-1 py-2 px-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                </button>
                <a href="index.php" class="flex-1 py-2 px-4 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>