-- ============================================
-- TIER 2 MIGRATION: Run after initial schema
-- ============================================

-- Maintenance Schedule Table
CREATE TABLE IF NOT EXISTS maintenance_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sarpras_id INT NOT NULL,
    jenis_maintenance ENUM('preventif', 'kalibrasi', 'pembersihan', 'penggantian_komponen', 'lainnya') NOT NULL,
    deskripsi TEXT,
    frekuensi ENUM('harian', 'mingguan', 'bulanan', 'triwulan', 'tahunan') NOT NULL,
    tanggal_terakhir DATE,
    tanggal_berikutnya DATE NOT NULL,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sarpras_id) REFERENCES sarpras(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Maintenance Log Table
CREATE TABLE IF NOT EXISTS maintenance_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT,
    sarpras_id INT NOT NULL,
    tgl_maintenance DATE NOT NULL,
    jenis_maintenance VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    biaya DECIMAL(12,2) DEFAULT 0,
    hasil ENUM('berhasil', 'perlu_perbaikan', 'gagal') NOT NULL,
    catatan TEXT,
    foto VARCHAR(255),
    performed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_id) REFERENCES maintenance_schedule(id) ON DELETE SET NULL,
    FOREIGN KEY (sarpras_id) REFERENCES sarpras(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Inspection Checklist Table
CREATE TABLE IF NOT EXISTS inspection_checklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    peminjaman_id INT NOT NULL,
    tipe ENUM('serah_terima', 'pengembalian') NOT NULL,
    kondisi_fisik ENUM('baik', 'cacat_minor', 'rusak') NOT NULL,
    kelengkapan ENUM('lengkap', 'tidak_lengkap') NOT NULL,
    fungsi ENUM('normal', 'terganggu', 'tidak_fungsi') NOT NULL,
    catatan TEXT,
    foto VARCHAR(255),
    inspected_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE,
    FOREIGN KEY (inspected_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Indexes for Tier 2 tables
CREATE INDEX idx_maintenance_schedule_sarpras ON maintenance_schedule(sarpras_id);
CREATE INDEX idx_maintenance_schedule_next ON maintenance_schedule(tanggal_berikutnya);
CREATE INDEX idx_maintenance_log_sarpras ON maintenance_log(sarpras_id);
CREATE INDEX idx_maintenance_log_date ON maintenance_log(tgl_maintenance);
CREATE INDEX idx_inspection_peminjaman ON inspection_checklist(peminjaman_id);
