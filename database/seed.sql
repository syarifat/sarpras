-- ============================================
-- SEED DATA: SARPRAS MANAGEMENT SYSTEM
-- ============================================

USE sarpras_db;

-- ============================================
-- USERS (password: password123)
-- ============================================
INSERT INTO users (username, password, email, nama_lengkap, role, phone) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@smk.sch.id', 'Administrator Sistem', 'admin', '081234567890'),
('petugas1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'petugas1@smk.sch.id', 'Budi Santoso', 'petugas', '081234567891'),
('petugas2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'petugas2@smk.sch.id', 'Dewi Lestari', 'petugas', '081234567892'),
('guru1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guru1@smk.sch.id', 'Ahmad Hidayat', 'user', '081234567893'),
('guru2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guru2@smk.sch.id', 'Siti Rahayu', 'user', '081234567894'),
('siswa1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siswa1@smk.sch.id', 'Rizki Pratama', 'user', '081234567895'),
('siswa2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siswa2@smk.sch.id', 'Putri Ayu', 'user', '081234567896');

-- ============================================
-- KATEGORI SARPRAS
-- ============================================
INSERT INTO kategori_sarpras (nama, deskripsi) VALUES
('Alat Lab Komputer', 'Perangkat dan alat untuk laboratorium komputer'),
('Alat Lab IPA', 'Perangkat dan alat untuk laboratorium IPA'),
('Perangkat TIK', 'Perangkat teknologi informasi dan komunikasi'),
('Buku Pelajaran', 'Buku-buku pelajaran dan referensi'),
('Furniture', 'Meja, kursi, dan perabotan lainnya'),
('Perlengkapan Ruang', 'Projector, whiteboard, dan perlengkapan ruang'),
('Alat Olahraga', 'Peralatan untuk kegiatan olahraga'),
('Alat Musik', 'Instrumen musik dan peralatan kesenian');

-- ============================================
-- SARPRAS (ITEMS)
-- ============================================
INSERT INTO sarpras (kode, nama, kategori_id, lokasi, deskripsi, jumlah_stok, kondisi) VALUES
-- Alat Lab Komputer
('LAB-PC-001', 'Komputer Desktop PC', 1, 'Lab Komputer 1', 'PC Desktop Intel Core i5, RAM 8GB, SSD 256GB', 20, 'baik'),
('LAB-PC-002', 'Monitor LED 24 inch', 1, 'Lab Komputer 1', 'Monitor LED Full HD', 20, 'baik'),
('LAB-PC-003', 'Keyboard USB', 1, 'Lab Komputer 1', 'Keyboard Standard USB', 25, 'baik'),
('LAB-PC-004', 'Mouse USB', 1, 'Lab Komputer 1', 'Mouse Optical USB', 25, 'baik'),

-- Perangkat TIK
('TIK-001', 'Laptop ASUS', 3, 'Ruang Guru', 'Laptop ASUS Core i7, RAM 16GB', 5, 'baik'),
('TIK-002', 'Laptop Lenovo', 3, 'Ruang Guru', 'Laptop Lenovo Core i5, RAM 8GB', 5, 'baik'),
('TIK-003', 'Tablet Android', 3, 'Ruang Multimedia', 'Tablet 10 inch untuk presentasi', 10, 'baik'),
('TIK-004', 'Kamera DSLR Canon', 3, 'Ruang Multimedia', 'Kamera Canon EOS 700D', 3, 'baik'),
('TIK-005', 'Tripod Kamera', 3, 'Ruang Multimedia', 'Tripod professional', 5, 'baik'),

-- Perlengkapan Ruang
('PRK-001', 'Proyektor Epson', 6, 'Gudang Umum', 'Proyektor Epson 3000 Lumens', 8, 'baik'),
('PRK-002', 'Layar Proyektor', 6, 'Gudang Umum', 'Layar proyektor 70 inch', 8, 'baik'),
('PRK-003', 'Wireless Presenter', 6, 'Gudang Umum', 'Logitech Wireless Presenter', 10, 'baik'),
('PRK-004', 'Speaker Portable', 6, 'Gudang Umum', 'Speaker Bluetooth 20W', 5, 'baik'),
('PRK-005', 'Microphone Wireless', 6, 'Gudang Umum', 'Mic Wireless Shure', 4, 'baik'),

-- Buku Pelajaran
('BK-001', 'Buku Pemrograman Web', 4, 'Perpustakaan', 'Buku panduan pemrograman web HTML CSS JS', 15, 'baik'),
('BK-002', 'Buku Database MySQL', 4, 'Perpustakaan', 'Buku panduan database MySQL', 15, 'baik'),
('BK-003', 'Buku Jaringan Komputer', 4, 'Perpustakaan', 'Buku panduan jaringan komputer', 10, 'baik'),
('BK-004', 'Buku Matematika SMK', 4, 'Perpustakaan', 'Buku matematika kelas X-XII', 30, 'baik'),

-- Alat Lab IPA
('IPA-001', 'Mikroskop', 2, 'Lab IPA', 'Mikroskop binokuler', 10, 'baik'),
('IPA-002', 'Tabung Reaksi Set', 2, 'Lab IPA', 'Set tabung reaksi 50pcs', 20, 'baik'),
('IPA-003', 'Bunsen Burner', 2, 'Lab IPA', 'Pembakar bunsen', 10, 'baik'),

-- Alat Olahraga
('OR-001', 'Bola Futsal', 7, 'Gudang Olahraga', 'Bola futsal Mikasa', 10, 'baik'),
('OR-002', 'Bola Basket', 7, 'Gudang Olahraga', 'Bola basket Molten', 8, 'baik'),
('OR-003', 'Bola Voli', 7, 'Gudang Olahraga', 'Bola voli Mikasa', 8, 'baik'),
('OR-004', 'Net Badminton', 7, 'Gudang Olahraga', 'Net badminton standard', 4, 'baik'),
('OR-005', 'Raket Badminton', 7, 'Gudang Olahraga', 'Raket badminton Yonex', 20, 'baik');

-- ============================================
-- SAMPLE PEMINJAMAN
-- ============================================
INSERT INTO peminjaman (kode_peminjaman, user_id, sarpras_id, jumlah, tgl_pinjam, tgl_kembali_rencana, tujuan, status, approved_by, approved_at) VALUES
('PJM-2026-001', 4, 10, 1, '2026-02-01', '2026-02-03', 'Presentasi materi di kelas XI RPL', 'returned', 1, '2026-02-01 08:00:00'),
('PJM-2026-002', 5, 5, 1, '2026-02-02', '2026-02-04', 'Persiapan mengajar online', 'returned', 2, '2026-02-02 09:00:00'),
('PJM-2026-003', 6, 15, 2, '2026-02-03', '2026-02-05', 'Tugas praktikum web programming', 'active', 1, '2026-02-03 10:00:00'),
('PJM-2026-004', 7, 22, 2, '2026-02-03', '2026-02-03', 'Latihan futsal ekskul', 'active', 2, '2026-02-03 14:00:00'),
('PJM-2026-005', 4, 8, 1, '2026-02-04', '2026-02-06', 'Dokumentasi kegiatan sekolah', 'pending', NULL, NULL);

-- ============================================
-- SAMPLE PENGEMBALIAN
-- ============================================
INSERT INTO pengembalian (peminjaman_id, tgl_pengembalian, kondisi_alat, deskripsi_kerusakan, catatan_petugas, processed_by) VALUES
(1, '2026-02-03', 'baik', NULL, 'Dikembalikan tepat waktu dalam kondisi baik', 1),
(2, '2026-02-04', 'rusak_ringan', 'Baterai laptop sudah lemah, perlu diganti', 'Perlu maintenance baterai', 2);

-- ============================================
-- SAMPLE PENGADUAN
-- ============================================
INSERT INTO pengaduan (user_id, judul, deskripsi, lokasi, jenis_sarpras, status) VALUES
(4, 'AC Lab Komputer 1 Tidak Dingin', 'AC di Lab Komputer 1 sudah tidak dingin lagi, suhu ruangan panas', 'Lab Komputer 1', 'AC/Pendingin Ruangan', 'proses'),
(5, 'Proyektor Kelas X RPL 2 Bermasalah', 'Proyektor sering mati sendiri setelah 30 menit pemakaian', 'Kelas X RPL 2', 'Proyektor', 'pending'),
(6, 'Kursi Rusak di Kelas XI TKJ 1', 'Ada 3 kursi yang patah kakinya, berbahaya untuk diduduki', 'Kelas XI TKJ 1', 'Furniture', 'selesai');

-- ============================================
-- SAMPLE CATATAN PENGADUAN
-- ============================================
INSERT INTO catatan_pengaduan (pengaduan_id, user_id, catatan) VALUES
(1, 2, 'Sudah dilaporkan ke teknisi AC, akan dicek besok'),
(1, 1, 'Teknisi sudah datang, AC perlu ganti freon'),
(3, 2, 'Kursi sudah diganti dengan yang baru');

-- ============================================
-- SAMPLE ACTIVITY LOG
-- ============================================
INSERT INTO activity_log (user_id, aksi, deskripsi, ip_address) VALUES
(1, 'LOGIN', 'Admin login ke sistem', '127.0.0.1'),
(4, 'LOGIN', 'User login ke sistem', '127.0.0.1'),
(4, 'PEMINJAMAN', 'Mengajukan peminjaman Proyektor Epson', '127.0.0.1'),
(1, 'APPROVE_PEMINJAMAN', 'Menyetujui peminjaman PJM-2026-001', '127.0.0.1'),
(4, 'PENGADUAN', 'Mengajukan pengaduan AC Lab Komputer 1', '127.0.0.1');
