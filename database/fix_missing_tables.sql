-- ============================================
-- FIX: Tambahkan tabel dan kolom yang kurang
-- Jalankan ini di phpMyAdmin InfinityFree
-- ============================================

-- Tambah kolom jenis_sarpras ke pengaduan jika belum ada
ALTER TABLE pengaduan ADD COLUMN IF NOT EXISTS jenis_sarpras VARCHAR(100) AFTER lokasi;

-- Ubah ENUM status pengaduan
ALTER TABLE pengaduan MODIFY COLUMN status ENUM('pending', 'proses', 'selesai', 'ditutup') DEFAULT 'pending';

-- Buat tabel catatan_pengaduan
CREATE TABLE IF NOT EXISTS catatan_pengaduan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pengaduan_id INT NOT NULL,
    user_id INT NOT NULL,
    catatan TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pengaduan_id) REFERENCES pengaduan(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
