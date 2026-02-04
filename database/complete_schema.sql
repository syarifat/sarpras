-- ============================================
-- SARPRAS COMPLETE SCHEMA (Tier 1 + Tier 2)
-- ============================================

-- Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    role ENUM('admin', 'petugas', 'user') NOT NULL DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Kategori Sarpras
CREATE TABLE IF NOT EXISTS kategori_sarpras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Sarpras
CREATE TABLE IF NOT EXISTS sarpras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(50) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    kategori_id INT,
    deskripsi TEXT,
    jumlah_stok INT DEFAULT 1,
    jumlah_tersedia INT DEFAULT 1,
    kondisi ENUM('baik', 'rusak_ringan', 'rusak_berat') DEFAULT 'baik',
    lokasi VARCHAR(100),
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori_sarpras(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Peminjaman
CREATE TABLE IF NOT EXISTS peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_peminjaman VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    sarpras_id INT NOT NULL,
    jumlah INT DEFAULT 1,
    tgl_pinjam DATE NOT NULL,
    tgl_kembali_rencana DATE NOT NULL,
    tgl_kembali_aktual DATE,
    tujuan TEXT,
    status ENUM('pending', 'approved', 'active', 'returned', 'rejected', 'overdue') DEFAULT 'pending',
    catatan_admin TEXT,
    approved_by INT,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sarpras_id) REFERENCES sarpras(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Pengembalian
CREATE TABLE IF NOT EXISTS pengembalian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    peminjaman_id INT NOT NULL UNIQUE,
    tgl_pengembalian DATE NOT NULL,
    kondisi_alat ENUM('baik', 'rusak_ringan', 'rusak_berat', 'hilang') NOT NULL,
    deskripsi_kerusakan TEXT,
    foto_pengembalian VARCHAR(255),
    diterima_oleh INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE,
    FOREIGN KEY (diterima_oleh) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Pengaduan
CREATE TABLE IF NOT EXISTS pengaduan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT NOT NULL,
    lokasi VARCHAR(100),
    foto VARCHAR(255),
    status ENUM('pending', 'proses', 'selesai', 'ditolak') DEFAULT 'pending',
    prioritas ENUM('rendah', 'sedang', 'tinggi') DEFAULT 'sedang',
    catatan_admin TEXT,
    handled_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (handled_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Activity Log
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    aksi VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Maintenance Schedule (Tier 2)
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

-- Maintenance Log (Tier 2)
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

-- Inspection Checklist (Tier 2)
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
