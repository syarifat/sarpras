-- ============================================
-- SARPRAS COMPLETE SEED DATA (Tier 1 + Tier 2)
-- ============================================

-- Users (password = 'password')
INSERT INTO users (username, password, nama_lengkap, email, phone, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@sarpras.local', '081234567890', 'admin'),
('petugas1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso', 'budi@sarpras.local', '081234567891', 'petugas'),
('guru1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Siti Aminah', 'siti@sarpras.local', '081234567892', 'user'),
('guru2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ahmad Fadli', 'ahmad@sarpras.local', '081234567893', 'user'),
('siswa1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rina Wati', 'rina@sarpras.local', '081234567894', 'user');

-- Kategori
INSERT INTO kategori_sarpras (nama, deskripsi) VALUES
('Elektronik', 'Peralatan elektronik seperti laptop, proyektor, dll'),
('Furniture', 'Perabotan seperti meja, kursi, lemari'),
('Alat Tulis', 'Perlengkapan tulis menulis'),
('Olahraga', 'Peralatan olahraga'),
('Laboratorium', 'Peralatan lab IPA, komputer, dll');

-- Sarpras
INSERT INTO sarpras (kode, nama, kategori_id, deskripsi, jumlah_stok, jumlah_tersedia, kondisi, lokasi) VALUES
('ELK-001', 'Laptop ASUS VivoBook', 1, 'Laptop untuk keperluan presentasi', 5, 4, 'baik', 'Gudang IT'),
('ELK-002', 'Proyektor Epson EB-X51', 1, 'Proyektor 3LCD XGA', 3, 3, 'baik', 'Gudang IT'),
('ELK-003', 'Speaker Portable JBL', 1, 'Speaker bluetooth portable', 4, 4, 'baik', 'Gudang IT'),
('FRN-001', 'Meja Lipat', 2, 'Meja lipat untuk acara', 20, 18, 'baik', 'Gudang Umum'),
('FRN-002', 'Kursi Lipat', 2, 'Kursi lipat plastik', 50, 45, 'baik', 'Gudang Umum'),
('OLR-001', 'Bola Basket Molten', 4, 'Bola basket official size 7', 10, 9, 'baik', 'Gudang Olahraga'),
('OLR-002', 'Net Badminton', 4, 'Net badminton standar', 5, 5, 'baik', 'Gudang Olahraga'),
('LAB-001', 'Mikroskop Binokuler', 5, 'Mikroskop untuk praktikum biologi', 15, 15, 'baik', 'Lab Biologi'),
('LAB-002', 'Komputer Desktop', 5, 'PC untuk lab komputer', 30, 28, 'baik', 'Lab Komputer');

-- Peminjaman
INSERT INTO peminjaman (kode_peminjaman, user_id, sarpras_id, jumlah, tgl_pinjam, tgl_kembali_rencana, tujuan, status, approved_by, approved_at) VALUES
('PJM-20260201-001', 3, 1, 1, '2026-02-01', '2026-02-03', 'Presentasi di kelas X', 'returned', 1, '2026-02-01 09:00:00'),
('PJM-20260202-002', 4, 2, 1, '2026-02-02', '2026-02-02', 'Rapat wali murid', 'active', 1, '2026-02-02 08:00:00'),
('PJM-20260203-003', 5, 6, 2, '2026-02-03', '2026-02-05', 'Latihan basket', 'pending', NULL, NULL);

-- Pengembalian
INSERT INTO pengembalian (peminjaman_id, tgl_pengembalian, kondisi_alat, diterima_oleh) VALUES
(1, '2026-02-03', 'baik', 2);

-- Pengaduan
INSERT INTO pengaduan (user_id, judul, deskripsi, lokasi, status, prioritas) VALUES
(3, 'AC Kelas X-A Rusak', 'AC di kelas X-A tidak dingin, perlu diservis', 'Kelas X-A', 'pending', 'sedang'),
(4, 'Proyektor Error', 'Proyektor di ruang rapat tiba-tiba mati', 'Ruang Rapat', 'proses', 'tinggi');

-- Maintenance Schedule
INSERT INTO maintenance_schedule (sarpras_id, jenis_maintenance, deskripsi, frekuensi, tanggal_terakhir, tanggal_berikutnya, status, created_by) VALUES
(1, 'preventif', 'Pengecekan rutin laptop', 'bulanan', '2026-01-15', '2026-02-15', 'aktif', 1),
(2, 'pembersihan', 'Pembersihan lensa proyektor', 'mingguan', '2026-02-01', '2026-02-08', 'aktif', 1),
(8, 'kalibrasi', 'Kalibrasi mikroskop', 'triwulan', '2025-11-10', '2026-02-10', 'aktif', 1);

-- Maintenance Log
INSERT INTO maintenance_log (schedule_id, sarpras_id, tgl_maintenance, jenis_maintenance, deskripsi, biaya, hasil, catatan, performed_by) VALUES
(1, 1, '2026-01-15', 'preventif', 'Update software dan cek hardware', 0, 'berhasil', 'Semua normal', 1),
(2, 2, '2026-02-01', 'pembersihan', 'Pembersihan lensa dan filter', 25000, 'berhasil', 'Gambar jadi lebih jernih', 2);

-- Inspection Checklist
INSERT INTO inspection_checklist (peminjaman_id, tipe, kondisi_fisik, kelengkapan, fungsi, catatan, inspected_by) VALUES
(1, 'serah_terima', 'baik', 'lengkap', 'normal', 'Kondisi prima', 1),
(1, 'pengembalian', 'baik', 'lengkap', 'normal', 'Dikembalikan dalam kondisi baik', 2);
