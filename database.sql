-- =========================================================
-- Basis data proyek CRUD Data Mahasiswa
-- Impor lewat phpMyAdmin (tab Import) atau:
--   mysql -u root < database.sql
-- =========================================================

CREATE DATABASE IF NOT EXISTS db_kampus
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_kampus;

DROP TABLE IF EXISTS mahasiswa;

-- Tabel data mahasiswa
CREATE TABLE mahasiswa (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nim        CHAR(10)     NOT NULL UNIQUE,
  nama       VARCHAR(100) NOT NULL,
  prodi      VARCHAR(50)  NOT NULL,
  angkatan   SMALLINT     NOT NULL,
  email      VARCHAR(100) NULL,
  dibuat     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO mahasiswa (nim, nama, prodi, angkatan, email) VALUES
('2205101001', 'Andi Siregar',      'Teknik Komputer',       2022, 'andi@students.polmed.ac.id'),
('2205101002', 'Butet Simanjuntak', 'Teknik Komputer',       2022, 'butet@students.polmed.ac.id'),
('2305102003', 'Citra Lubis',       'Teknik Informatika',    2023, NULL),
('2305103004', 'Daniel Hutapea',    'Manajemen Informatika', 2023, 'daniel@students.polmed.ac.id'),
('2405101005', 'Eka Nasution',      'Teknik Komputer',       2024, NULL);
