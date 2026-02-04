-- ============================================
-- TIER 2 SEED DATA
-- Run after tier2_migration.sql
-- ============================================

USE sarpras_db;

-- ============================================
-- MAINTENANCE SCHEDULE SAMPLE DATA
-- ============================================
INSERT INTO maintenance_schedule (sarpras_id, jenis_maintenance, deskripsi, frekuensi, tanggal_terakhir, tanggal_berikutnya, status, created_by) VALUES
(1, 'preventif', 'Pengecekan rutin kondisi fisik dan fungsi', 'bulanan', '2026-01-15', '2026-02-15', 'aktif', 1),
(1, 'pembersihan', 'Pembersihan menyeluruh dan perawatan dasar', 'mingguan', '2026-02-01', '2026-02-08', 'aktif', 1),
(2, 'kalibrasi', 'Kalibrasi dan penyesuaian akurasi', 'triwulan', '2025-11-10', '2026-02-10', 'aktif', 1),
(3, 'preventif', 'Pemeriksaan komponen dan fungsi dasar', 'bulanan', '2026-01-20', '2026-02-20', 'aktif', 1),
(4, 'penggantian_komponen', 'Penggantian filter dan komponen habis pakai', 'tahunan', '2025-03-01', '2026-03-01', 'aktif', 1),
(5, 'pembersihan', 'Pembersihan dan sanitasi', 'harian', '2026-02-04', '2026-02-05', 'aktif', 2);

-- ============================================
-- MAINTENANCE LOG SAMPLE DATA
-- ============================================
INSERT INTO maintenance_log (schedule_id, sarpras_id, tgl_maintenance, jenis_maintenance, deskripsi, biaya, hasil, catatan, performed_by) VALUES
(1, 1, '2026-01-15', 'preventif', 'Pengecekan kondisi fisik, kabel, dan fungsi tombol', 0, 'berhasil', 'Semua komponen berfungsi normal', 1),
(2, 1, '2026-01-25', 'pembersihan', 'Pembersihan debu dan kotoran', 25000, 'berhasil', 'Menggunakan alat pembersih khusus', 2),
(2, 1, '2026-02-01', 'pembersihan', 'Pembersihan rutin mingguan', 0, 'berhasil', NULL, 2),
(3, 2, '2025-11-10', 'kalibrasi', 'Kalibrasi akurasi pengukuran', 150000, 'berhasil', 'Hasil kalibrasi dalam batas toleransi', 1),
(NULL, 3, '2026-01-05', 'perbaikan', 'Perbaikan engsel yang longgar', 75000, 'berhasil', 'Mengganti baut dan mengencangkan engsel', 2),
(4, 3, '2026-01-20', 'preventif', 'Pemeriksaan rutin bulanan', 0, 'berhasil', 'Kondisi baik setelah perbaikan', 1),
(NULL, 4, '2025-12-15', 'perbaikan', 'Perbaikan motor yang tidak berputar', 350000, 'perlu_perbaikan', 'Motor sudah diganti, perlu observasi lanjutan', 1),
(6, 5, '2026-02-04', 'pembersihan', 'Pembersihan dan desinfektan', 15000, 'berhasil', 'Sanitasi menggunakan cairan khusus', 2);

-- ============================================
-- INSPECTION CHECKLIST SAMPLE DATA
-- (Linked to peminjaman that exist in seed.sql)
-- ============================================
-- Note: Run this only if you have peminjaman with id 1, 2, 3 from original seed
INSERT INTO inspection_checklist (peminjaman_id, tipe, kondisi_fisik, kelengkapan, fungsi, catatan, inspected_by) VALUES
(1, 'serah_terima', 'baik', 'lengkap', 'normal', 'Kondisi prima saat serah terima', 1),
(1, 'pengembalian', 'baik', 'lengkap', 'normal', 'Dikembalikan dalam kondisi baik', 2),
(2, 'serah_terima', 'baik', 'lengkap', 'normal', 'Semua komponen lengkap dan berfungsi', 1),
(2, 'pengembalian', 'cacat_minor', 'lengkap', 'normal', 'Ada goresan kecil di permukaan', 2),
(3, 'serah_terima', 'baik', 'lengkap', 'normal', NULL, 2);
