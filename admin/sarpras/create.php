<?php

/**
 * Admin - Create Sarpras
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Tambah Sarpras');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin']);

$kategoriList = fetchAll("SELECT * FROM kategori_sarpras ORDER BY nama ASC");
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $kode = sanitize($_POST['kode'] ?? '');
        $nama = sanitize($_POST['nama'] ?? '');
        $kategori_id = intval($_POST['kategori_id'] ?? 0);
        $lokasi = sanitize($_POST['lokasi'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $jumlah_stok = intval($_POST['jumlah_stok'] ?? 0);
        $kondisi = sanitize($_POST['kondisi'] ?? 'baik');

        // Validation
        if (empty($kode) || empty($nama) || $kategori_id <= 0) {
            $error = 'Kode, nama, dan kategori harus diisi.';
        } elseif ($jumlah_stok < 0) {
            $error = 'Jumlah stok tidak boleh negatif.';
        } else {
            // Check if code exists
            $existing = fetch("SELECT id FROM sarpras WHERE kode = ?", [$kode]);
            if ($existing) {
                $error = 'Kode sarpras sudah digunakan.';
            } else {
                // Handle file upload
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
                        "INSERT INTO sarpras (kode, nama, kategori_id, lokasi, deskripsi, jumlah_stok, kondisi, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                        [$kode, $nama, $kategori_id, $lokasi, $deskripsi, $jumlah_stok, $kondisi, $foto]
                    );

                    logActivity('CREATE_SARPRAS', "Created sarpras: $kode - $nama");
                    setFlash('success', 'Sarpras berhasil ditambahkan.');
                    header('Location: index.php');
                    exit;
                }
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tambah Sarpras</h1>
            <p class="text-gray-600">Tambah data sarana prasarana baru</p>
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kode Sarpras *</label>
                        <input type="text" name="kode" value="<?= e($_POST['kode'] ?? '') ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required placeholder="Contoh: LAB-PC-001">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
                        <select name="kategori_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($kategoriList as $kat): ?>
                                <option value="<?= $kat['id'] ?>" <?= ($_POST['kategori_id'] ?? '') == $kat['id'] ? 'selected' : '' ?>><?= e($kat['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Sarpras *</label>
                    <input type="text" name="nama" value="<?= e($_POST['nama'] ?? '') ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                    <input type="text" name="lokasi" value="<?= e($_POST['lokasi'] ?? '') ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Contoh: Lab Komputer 1, Perpustakaan">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="deskripsi" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= e($_POST['deskripsi'] ?? '') ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Stok *</label>
                        <input type="number" name="jumlah_stok" value="<?= e($_POST['jumlah_stok'] ?? '1') ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required min="0">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kondisi Awal *</label>
                        <select name="kondisi" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="baik" <?= ($_POST['kondisi'] ?? '') === 'baik' ? 'selected' : '' ?>>Baik</option>
                            <option value="rusak_ringan" <?= ($_POST['kondisi'] ?? '') === 'rusak_ringan' ? 'selected' : '' ?>>Rusak Ringan</option>
                            <option value="rusak_berat" <?= ($_POST['kondisi'] ?? '') === 'rusak_berat' ? 'selected' : '' ?>>Rusak Berat</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Foto</label>
                    <input type="file" name="foto" accept="image/*"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, GIF. Maksimal 5MB.</p>
                </div>
            </div>

            <div class="mt-8 flex gap-4">
                <button type="submit" class="flex-1 py-2 px-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
                <a href="index.php" class="flex-1 py-2 px-4 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>