-- ============================================
-- DATABASE SCHEMA: SARPRAS MANAGEMENT SYSTEM
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS sarpras_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sarpras_db;

-- ============================================
-- USERS TABLE
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('admin', 'petugas', 'user') NOT NULL DEFAULT 'user',
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- KATEGORI SARPRAS TABLE
-- ============================================
CREATE TABLE kategori_sarpras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- SARPRAS (ITEMS) TABLE
-- ============================================
CREATE TABLE sarpras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(50) NOT NULL UNIQUE,
    nama VARCHAR(150) NOT NULL,
    kategori_id INT NOT NULL,
    lokasi VARCHAR(100),
    deskripsi TEXT,
    jumlah_stok INT NOT NULL DEFAULT 0,
    kondisi ENUM('baik', 'rusak_ringan', 'rusak_berat') DEFAULT 'baik',
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori_sarpras(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================
-- PEMINJAMAN (BORROWING) TABLE
-- ============================================
CREATE TABLE peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_peminjaman VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    sarpras_id INT NOT NULL,
    jumlah INT NOT NULL DEFAULT 1,
    tgl_pinjam DATE NOT NULL,
    tgl_kembali_rencana DATE NOT NULL,
    tgl_kembali_aktual DATE,
    tujuan TEXT,
    status ENUM('pending', 'approved', 'rejected', 'active', 'returned', 'overdue') DEFAULT 'pending',
    catatan_admin TEXT,
    approved_by INT,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (sarpras_id) REFERENCES sarpras(id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- PENGEMBALIAN (RETURN) TABLE
-- ============================================
CREATE TABLE pengembalian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    peminjaman_id INT NOT NULL,
    tgl_pengembalian DATE NOT NULL,
    kondisi_alat ENUM('baik', 'rusak_ringan', 'rusak_berat', 'hilang') NOT NULL,
    deskripsi_kerusakan TEXT,
    foto_pengembalian VARCHAR(255),
    catatan_petugas TEXT,
    processed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE RESTRICT,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- PENGADUAN (COMPLAINTS) TABLE
-- ============================================
CREATE TABLE pengaduan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT NOT NULL,
    lokasi VARCHAR(100),
    jenis_sarpras VARCHAR(100),
    foto VARCHAR(255),
    status ENUM('pending', 'proses', 'selesai', 'ditutup') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================
-- CATATAN PENGADUAN TABLE
-- ============================================
CREATE TABLE catatan_pengaduan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pengaduan_id INT NOT NULL,
    user_id INT NOT NULL,
    catatan TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pengaduan_id) REFERENCES pengaduan(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================
-- ACTIVITY LOG TABLE
-- ============================================
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    aksi VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- INDEXES FOR BETTER PERFORMANCE
-- ============================================
CREATE INDEX idx_peminjaman_user ON peminjaman(user_id);
CREATE INDEX idx_peminjaman_sarpras ON peminjaman(sarpras_id);
CREATE INDEX idx_peminjaman_status ON peminjaman(status);
CREATE INDEX idx_peminjaman_tanggal ON peminjaman(tgl_pinjam, tgl_kembali_rencana);
CREATE INDEX idx_pengaduan_user ON pengaduan(user_id);
CREATE INDEX idx_pengaduan_status ON pengaduan(status);
CREATE INDEX idx_sarpras_kategori ON sarpras(kategori_id);
CREATE INDEX idx_activity_user ON activity_log(user_id);
CREATE INDEX idx_activity_created ON activity_log(created_at);

-- ============================================
-- TIER 2: MAINTENANCE SCHEDULE TABLE
-- ============================================
CREATE TABLE maintenance_schedule (
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

-- ============================================
-- TIER 2: MAINTENANCE LOG TABLE
-- ============================================
CREATE TABLE maintenance_log (
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

-- ============================================
-- TIER 2: INSPECTION CHECKLIST TABLE
-- ============================================
CREATE TABLE inspection_checklist (
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
