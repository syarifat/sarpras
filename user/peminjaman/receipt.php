<?php

/**
 * User - Print Receipt Peminjaman
 * Sarpras Management System
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['user']);

$id = intval($_GET['id'] ?? 0);
$userId = getCurrentUser()['id'];

$peminjaman = fetch("
    SELECT p.*, u.nama_lengkap, u.email, u.phone,
           s.nama as sarpras_nama, s.kode as sarpras_kode, s.lokasi as sarpras_lokasi,
           a.nama_lengkap as approved_by_name
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN sarpras s ON p.sarpras_id = s.id 
    LEFT JOIN users a ON p.approved_by = a.id
    WHERE p.id = ? AND p.user_id = ? AND p.status IN ('approved', 'active')
", [$id, $userId]);

if (!$peminjaman) {
    setFlash('error', 'Peminjaman tidak ditemukan atau belum disetujui.');
    header('Location: index.php');
    exit;
}

$qrData = $peminjaman['kode_peminjaman'];
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrData);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Peminjaman - <?= e($peminjaman['kode_peminjaman']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen p-4">
    <div class="max-w-2xl mx-auto">
        <div class="no-print mb-4 flex gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-print mr-2"></i>Cetak
            </button>
            <a href="index.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Kembali</a>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="text-center border-b-2 border-gray-800 pb-4 mb-6">
                <h1 class="text-2xl font-bold text-gray-800">SMK NEGERI 1</h1>
                <p class="text-gray-600">Sistem Manajemen Sarana Prasarana</p>
                <h2 class="text-xl font-bold text-blue-600 mt-4">BUKTI PEMINJAMAN</h2>
            </div>

            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-sm text-gray-500">Kode Peminjaman:</p>
                    <p class="text-2xl font-bold font-mono text-gray-800"><?= e($peminjaman['kode_peminjaman']) ?></p>
                    <p class="text-sm text-gray-500 mt-2">Tanggal Cetak: <?= date('d/m/Y H:i') ?></p>
                </div>
                <div class="text-center">
                    <img src="<?= $qrUrl ?>" alt="QR Code" class="w-32 h-32 border">
                    <p class="text-xs text-gray-500 mt-1">Scan untuk verifikasi</p>
                </div>
            </div>

            <table class="w-full mb-6">
                <tbody class="divide-y">
                    <tr>
                        <td class="py-2 text-gray-600 w-1/3">Peminjam</td>
                        <td class="py-2 font-medium">: <?= e($peminjaman['nama_lengkap']) ?></td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Telepon</td>
                        <td class="py-2">: <?= e($peminjaman['phone'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Sarpras</td>
                        <td class="py-2 font-medium">: <?= e($peminjaman['sarpras_nama']) ?> (<?= e($peminjaman['sarpras_kode']) ?>)</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Jumlah</td>
                        <td class="py-2">: <?= $peminjaman['jumlah'] ?> unit</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Lokasi Pengambilan</td>
                        <td class="py-2">: <?= e($peminjaman['sarpras_lokasi'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Tanggal Pinjam</td>
                        <td class="py-2 font-medium">: <?= formatDate($peminjaman['tgl_pinjam'], 'd F Y') ?></td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Tanggal Kembali</td>
                        <td class="py-2 font-medium">: <?= formatDate($peminjaman['tgl_kembali_rencana'], 'd F Y') ?></td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Tujuan</td>
                        <td class="py-2">: <?= e($peminjaman['tujuan'] ?? '-') ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="bg-gray-50 p-4 rounded-lg mb-6 text-sm">
                <p class="font-semibold mb-2">Ketentuan:</p>
                <ol class="list-decimal list-inside space-y-1 text-gray-600">
                    <li>Peminjam bertanggung jawab penuh atas barang yang dipinjam.</li>
                    <li>Barang harus dikembalikan tepat waktu sesuai tanggal yang ditentukan.</li>
                    <li>Kerusakan atau kehilangan menjadi tanggung jawab peminjam.</li>
                    <li>Tunjukkan bukti ini saat pengambilan dan pengembalian barang.</li>
                </ol>
            </div>

            <div class="grid grid-cols-2 gap-8 text-center mt-8">
                <div>
                    <p class="text-gray-600 mb-16">Peminjam,</p>
                    <p class="font-medium border-t pt-2"><?= e($peminjaman['nama_lengkap']) ?></p>
                </div>
                <div>
                    <p class="text-gray-600 mb-16">Petugas,</p>
                    <p class="font-medium border-t pt-2"><?= e($peminjaman['approved_by_name'] ?? '___________________') ?></p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>