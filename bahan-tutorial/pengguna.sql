USE db_kampus;

-- Tabel pengguna, dipakai API untuk login dan token
DROP TABLE IF EXISTS pengguna;
CREATE TABLE pengguna (
  id                 INT AUTO_INCREMENT PRIMARY KEY,
  username           VARCHAR(30)  NOT NULL UNIQUE,
  password_hash      VARCHAR(255) NOT NULL,
  peran              ENUM('admin','operator') NOT NULL,
  token              CHAR(64)     NULL,
  token_kedaluwarsa  DATETIME     NULL
) ENGINE=InnoDB;

-- Akun contoh:
--   admin    / admin123     (boleh tambah, ubah, hapus)
--   operator / operator123  (boleh tambah dan ubah, TIDAK boleh hapus)
INSERT INTO pengguna (username, password_hash, peran) VALUES
('admin',    '$2y$10$3Nb0wPykWkrdAK1U65g16uD3XualAD0ve9zS5SBfs1CxS8c8q0YdC', 'admin'),
('operator', '$2y$10$XgHsvDIUMTjAprPnv.FCJ.QrAkhok/KH8OHzVar/IOF1awra3vrYe', 'operator');
