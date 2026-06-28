CREATE DATABASE IF NOT EXISTS db_penduduk_desa;
USE db_penduduk_desa;

-- 1. Tabel profil_desa
CREATE TABLE IF NOT EXISTS profil_desa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_desa VARCHAR(100) DEFAULT '',
    kecamatan VARCHAR(100) DEFAULT '',
    kabupaten VARCHAR(100) DEFAULT '',
    provinsi VARCHAR(100) DEFAULT '',
    alamat TEXT DEFAULT NULL,
    telepon VARCHAR(20) DEFAULT '',
    email VARCHAR(100) DEFAULT '',
    website VARCHAR(100) DEFAULT '',
    logo VARCHAR(255) DEFAULT NULL,
    visi TEXT DEFAULT NULL,
    misi TEXT DEFAULT NULL,
    sejarah TEXT DEFAULT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Tabel penduduk
CREATE TABLE IF NOT EXISTS penduduk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nik VARCHAR(16) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    tempat_lahir VARCHAR(50) DEFAULT '',
    tanggal_lahir DATE DEFAULT NULL,
    jenis_kelamin ENUM('L','P') DEFAULT 'L',
    alamat TEXT DEFAULT NULL,
    rt VARCHAR(3) DEFAULT '',
    rw VARCHAR(3) DEFAULT '',
    dusun VARCHAR(50) DEFAULT '',
    agama VARCHAR(20) DEFAULT '',
    status_perkawinan VARCHAR(20) DEFAULT '',
    pekerjaan VARCHAR(50) DEFAULT '',
    kewarganegaraan VARCHAR(10) DEFAULT 'WNI',
    status_hidup ENUM('hidup','meninggal','pindah') DEFAULT 'hidup',
    tgl_input DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabel kematian
CREATE TABLE IF NOT EXISTS kematian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    penduduk_id INT NOT NULL,
    tgl_meninggal DATE DEFAULT NULL,
    tempat_meninggal VARCHAR(100) DEFAULT '',
    penyebab VARCHAR(100) DEFAULT '',
    keterangan TEXT DEFAULT NULL,
    tgl_input DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (penduduk_id) REFERENCES penduduk(id) ON DELETE CASCADE
);

-- 4. Tabel pindah
CREATE TABLE IF NOT EXISTS pindah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    penduduk_id INT NOT NULL,
    tgl_pindah DATE DEFAULT NULL,
    alamat_tujuan TEXT DEFAULT NULL,
    alasan_pindah VARCHAR(100) DEFAULT '',
    keterangan TEXT DEFAULT NULL,
    tgl_input DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (penduduk_id) REFERENCES penduduk(id) ON DELETE CASCADE
);

-- 5. Tabel users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','penduduk') NOT NULL DEFAULT 'penduduk',
    penduduk_id INT DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (penduduk_id) REFERENCES penduduk(id) ON DELETE SET NULL
);

-- Data default: profil desa
INSERT INTO profil_desa (nama_desa, kecamatan, kabupaten, provinsi, alamat, visi, misi)
VALUES ('Desa Contoh', 'Kecamatan Contoh', 'Kabupaten Contoh', 'Provinsi Contoh', 'Alamat Desa Contoh', 'Menjadi desa yang maju, mandiri, dan sejahtera', '1. Meningkatkan pelayanan masyarakat\n2. Membangun infrastruktur desa\n3. Meningkatkan perekonomian desa');

-- Data default: user admin (password: admin123)
INSERT INTO penduduk (nik, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, rt, rw, dusun, agama, status_perkawinan, pekerjaan)
VALUES ('0000000000000000', 'Admin Desa', 'Kota Admin', '2000-01-01', 'L', 'Alamat Admin', '001', '001', 'Dusun Admin', 'Islam', 'Kawin', 'Perangkat Desa');

INSERT INTO users (username, password, role, penduduk_id)
VALUES ('admin', '$2y$10$VtKBpwkvHbXFOcWWNRvvzeVPdRZySk8AMSV.QEv3y0/i1/G0P6buq', 'admin', 1);
