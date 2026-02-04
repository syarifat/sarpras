<?php

/**
 * Admin - Export Reports
 * Sarpras Management System - Tier 2
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(['admin']);

$type = sanitize($_GET['type'] ?? '');
$format = sanitize($_GET['format'] ?? 'csv');
$startDate = sanitize($_GET['start_date'] ?? date('Y-m-01'));
$endDate = sanitize($_GET['end_date'] ?? date('Y-m-d'));

if (!$type) {
    setFlash('error', 'Tipe export tidak valid.');
    header('Location: index.php');
    exit;
}

// Get data based on type
$data = [];
$headers = [];
$filename = '';

switch ($type) {
    case 'peminjaman':
        $headers = ['Kode', 'Peminjam', 'Sarpras', 'Jumlah', 'Tgl Pinjam', 'Tgl Kembali', 'Status', 'Tujuan'];
        $filename = "laporan_peminjaman_{$startDate}_{$endDate}";
        $rows = fetchAll("
            SELECT p.kode_peminjaman, u.nama_lengkap, s.nama as sarpras, p.jumlah, 
                   p.tgl_pinjam, p.tgl_kembali_rencana, p.status, p.tujuan
            FROM peminjaman p
            JOIN users u ON p.user_id = u.id
            JOIN sarpras s ON p.sarpras_id = s.id
            WHERE DATE(p.created_at) BETWEEN ? AND ?
            ORDER BY p.created_at DESC
        ", [$startDate, $endDate]);
        foreach ($rows as $row) {
            $data[] = [
                $row['kode_peminjaman'],
                $row['nama_lengkap'],
                $row['sarpras'],
                $row['jumlah'],
                $row['tgl_pinjam'],
                $row['tgl_kembali_rencana'],
                $row['status'],
                $row['tujuan']
            ];
        }
        break;

    case 'pengaduan':
        $headers = ['ID', 'Pelapor', 'Judul', 'Lokasi', 'Status', 'Tanggal'];
        $filename = "laporan_pengaduan_{$startDate}_{$endDate}";
        $rows = fetchAll("
            SELECT pg.id, u.nama_lengkap, pg.judul, pg.lokasi, pg.status, pg.created_at
            FROM pengaduan pg
            JOIN users u ON pg.user_id = u.id
            WHERE DATE(pg.created_at) BETWEEN ? AND ?
            ORDER BY pg.created_at DESC
        ", [$startDate, $endDate]);
        foreach ($rows as $row) {
            $data[] = [
                $row['id'],
                $row['nama_lengkap'],
                $row['judul'],
                $row['lokasi'],
                $row['status'],
                $row['created_at']
            ];
        }
        break;

    case 'sarpras':
        $headers = ['Kode', 'Nama', 'Kategori', 'Lokasi', 'Stok', 'Kondisi'];
        $filename = "inventaris_sarpras_" . date('Y-m-d');
        $rows = fetchAll("
            SELECT s.kode, s.nama, k.nama as kategori, s.lokasi, s.jumlah_stok, s.kondisi
            FROM sarpras s
            LEFT JOIN kategori_sarpras k ON s.kategori_id = k.id
            ORDER BY s.nama
        ");
        foreach ($rows as $row) {
            $data[] = [
                $row['kode'],
                $row['nama'],
                $row['kategori'],
                $row['lokasi'],
                $row['jumlah_stok'],
                $row['kondisi']
            ];
        }
        break;

    case 'maintenance':
        $headers = ['Tanggal', 'Sarpras', 'Jenis', 'Hasil', 'Biaya', 'Petugas', 'Catatan'];
        $filename = "laporan_maintenance_{$startDate}_{$endDate}";
        $rows = fetchAll("
            SELECT ml.tgl_maintenance, s.nama, ml.jenis_maintenance, ml.hasil, ml.biaya, u.nama_lengkap, ml.catatan
            FROM maintenance_log ml
            JOIN sarpras s ON ml.sarpras_id = s.id
            LEFT JOIN users u ON ml.performed_by = u.id
            WHERE ml.tgl_maintenance BETWEEN ? AND ?
            ORDER BY ml.tgl_maintenance DESC
        ", [$startDate, $endDate]);
        foreach ($rows as $row) {
            $data[] = [
                $row['tgl_maintenance'],
                $row['nama'],
                $row['jenis_maintenance'],
                $row['hasil'],
                $row['biaya'],
                $row['nama_lengkap'],
                $row['catatan']
            ];
        }
        break;

    default:
        setFlash('error', 'Tipe export tidak valid.');
        header('Location: index.php');
        exit;
}

// Export to CSV
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

    $output = fopen('php://output', 'w');

    // Add BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Write headers
    fputcsv($output, $headers);

    // Write data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }

    fclose($output);

    logActivity('EXPORT_DATA', "Exported $type data as CSV");
    exit;
}

// Export to HTML (printable)
if ($format === 'print') {
    logActivity('EXPORT_DATA', "Printed $type data");
?>
    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <title><?= ucfirst($type) ?> Report</title>
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

    <body class="bg-white p-8">
        <div class="no-print mb-4">
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded">Cetak</button>
            <a href="index.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded ml-2">Kembali</a>
        </div>

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold">Laporan <?= ucfirst($type) ?></h1>
            <p class="text-gray-600">Periode: <?= formatDate($startDate, 'd M Y') ?> - <?= formatDate($endDate, 'd M Y') ?></p>
            <p class="text-sm text-gray-500">Dicetak: <?= date('d M Y H:i') ?></p>
        </div>

        <table class="w-full border-collapse border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <?php foreach ($headers as $h): ?>
                        <th class="border border-gray-300 px-3 py-2 text-left text-sm"><?= e($h) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <?php foreach ($row as $cell): ?>
                            <td class="border border-gray-300 px-3 py-2 text-sm"><?= e($cell) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p class="mt-4 text-sm text-gray-500">Total: <?= count($data) ?> data</p>
    </body>

    </html>
<?php
    exit;
}
