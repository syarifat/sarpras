<?php

/**
 * User - Create Peminjaman Request
 * Sarpras Management System
 */

define('PAGE_TITLE', 'Ajukan Peminjaman');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['user']);

// Get available sarpras
$sarpras = fetchAll("
    SELECT s.*, k.nama as kategori_nama 
    FROM sarpras s 
    LEFT JOIN kategori_sarpras k ON s.kategori_id = k.id 
    WHERE s.jumlah_stok > 0 AND s.kondisi = 'baik'
    ORDER BY k.nama, s.nama
");

$kategori = fetchAll("SELECT * FROM kategori_sarpras ORDER BY nama");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $sarpras_id = intval($_POST['sarpras_id'] ?? 0);
        $jumlah = intval($_POST['jumlah'] ?? 1);
        $tgl_pinjam = sanitize($_POST['tgl_pinjam'] ?? '');
        $tgl_kembali = sanitize($_POST['tgl_kembali'] ?? '');
        $tujuan = sanitize($_POST['tujuan'] ?? '');

        // Validate
        $selectedSarpras = fetch("SELECT * FROM sarpras WHERE id = ?", [$sarpras_id]);

        if (!$selectedSarpras) {
            $error = 'Sarpras tidak valid.';
        } elseif ($jumlah <= 0 || $jumlah > $selectedSarpras['jumlah_stok']) {
            $error = 'Jumlah tidak valid. Stok tersedia: ' . $selectedSarpras['jumlah_stok'];
        } elseif (empty($tgl_pinjam) || empty($tgl_kembali)) {
            $error = 'Tanggal pinjam dan kembali harus diisi.';
        } elseif (strtotime($tgl_pinjam) < strtotime('today')) {
            $error = 'Tanggal pinjam tidak boleh di masa lalu.';
        } elseif (strtotime($tgl_kembali) <= strtotime($tgl_pinjam)) {
            $error = 'Tanggal kembali harus lebih besar dari tanggal pinjam.';
        } elseif (empty($tujuan)) {
            $error = 'Tujuan peminjaman harus diisi.';
        } else {
            $kodePeminjaman = generateKode('PMJ');

            query(
                "INSERT INTO peminjaman (kode_peminjaman, user_id, sarpras_id, jumlah, tgl_pinjam, tgl_kembali_rencana, tujuan, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')",
                [$kodePeminjaman, getCurrentUser()['id'], $sarpras_id, $jumlah, $tgl_pinjam, $tgl_kembali, $tujuan]
            );

            logActivity('CREATE_PEMINJAMAN', "Created peminjaman request: $kodePeminjaman");
            setFlash('success', 'Peminjaman berhasil diajukan. Tunggu persetujuan admin.');
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
            <h1 class="text-2xl font-bold text-gray-800">Ajukan Peminjaman</h1>
            <p class="text-gray-600">Isi form untuk mengajukan peminjaman sarpras</p>
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
                <!-- Kategori Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter Kategori</label>
                    <select id="kategoriFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($kategori as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= e($k['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Sarpras Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Sarpras *</label>
                    <select name="sarpras_id" id="sarprasSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Pilih sarpras yang akan dipinjam</option>
                        <?php foreach ($sarpras as $s): ?>
                            <option value="<?= $s['id'] ?>" data-kategori="<?= $s['kategori_id'] ?>" data-stok="<?= $s['jumlah_stok'] ?>"
                                <?= ($_POST['sarpras_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                [<?= e($s['kode']) ?>] <?= e($s['nama']) ?> - <?= e($s['kategori_nama']) ?> (Stok: <?= $s['jumlah_stok'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Jumlah -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah *</label>
                    <input type="number" name="jumlah" id="jumlahInput" value="<?= e($_POST['jumlah'] ?? '1') ?>" min="1" max="100"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required>
                    <p id="stokInfo" class="text-xs text-gray-500 mt-1"></p>
                </div>

                <!-- Tanggal -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Pinjam *</label>
                        <input type="date" name="tgl_pinjam" value="<?= e($_POST['tgl_pinjam'] ?? date('Y-m-d')) ?>"
                            min="<?= date('Y-m-d') ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Kembali *</label>
                        <input type="date" name="tgl_kembali" value="<?= e($_POST['tgl_kembali'] ?? date('Y-m-d', strtotime('+7 days'))) ?>"
                            min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>
                </div>

                <!-- Tujuan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tujuan Peminjaman *</label>
                    <textarea name="tujuan" rows="3" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Jelaskan tujuan peminjaman sarpras..."><?= e($_POST['tujuan'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="mt-8 flex gap-4">
                <button type="submit" class="flex-1 py-3 px-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-paper-plane mr-2"></i>Ajukan Peminjaman
                </button>
                <a href="index.php" class="flex-1 py-3 px-4 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    // Filter sarpras by kategori
    document.getElementById('kategoriFilter').addEventListener('change', function() {
        const kategoriId = this.value;
        const options = document.getElementById('sarprasSelect').options;

        for (let i = 1; i < options.length; i++) {
            const option = options[i];
            if (!kategoriId || option.dataset.kategori === kategoriId) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        }
        document.getElementById('sarprasSelect').value = '';
    });

    // Update max jumlah based on stok
    document.getElementById('sarprasSelect').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const stok = selected.dataset.stok || '';
        const jumlahInput = document.getElementById('jumlahInput');
        const stokInfo = document.getElementById('stokInfo');

        if (stok) {
            jumlahInput.max = stok;
            stokInfo.textContent = 'Stok tersedia: ' + stok + ' unit';
        } else {
            stokInfo.textContent = '';
        }
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>