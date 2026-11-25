<?php
// Aturan bisnis data mahasiswa.
// Ditulis SATU KALI dalam fungsi, dipakai oleh tambah.php dan edit.php.

const DAFTAR_PRODI = ['Teknik Komputer', 'Teknik Informatika', 'Manajemen Informatika'];

/** true bila $v bilangan bulat: angka JSON (2023) atau teks berisi digit ("2023"). */
function bilanganBulat($v): bool {
    return is_int($v) || (is_string($v) && ctype_digit($v));
}

/**
 * Memeriksa data mahasiswa.
 * Mengembalikan array kosong bila valid, atau ['nama_kolom' => 'pesan kesalahan'].
 * $denganNim = false dipakai saat mengubah data (NIM diambil dari alamat, tidak diubah).
 */
function validasiMahasiswa(array $in, bool $denganNim = true): array {
    $galat = [];

    if ($denganNim) {
        $nim = $in['nim'] ?? '';
        if (!is_string($nim) || !preg_match('/^\d{10}$/', $nim)) {
            $galat['nim'] = 'NIM wajib diisi 10 digit angka (ditulis sebagai teks).';
        }
    }

    $nama = $in['nama'] ?? '';
    if (!is_string($nama) || mb_strlen(trim($nama)) < 3 || mb_strlen(trim($nama)) > 100) {
        $galat['nama'] = 'Nama wajib diisi 3 sampai 100 karakter.';
    } elseif (!preg_match("/^[\p{L} .'-]+$/u", trim($nama))) {
        $galat['nama'] = 'Nama hanya boleh berisi huruf, spasi, titik, tanda petik, dan tanda hubung.';
    }

    $prodi = $in['prodi'] ?? '';
    if (!in_array($prodi, DAFTAR_PRODI, true)) {
        $galat['prodi'] = 'Prodi harus salah satu dari: ' . implode(', ', DAFTAR_PRODI) . '.';
    }

    $angkatan = $in['angkatan'] ?? null;
    $tahunIni = (int) date('Y');
    if (!bilanganBulat($angkatan)) {
        $galat['angkatan'] = 'Angkatan harus berupa angka.';
    } elseif ((int) $angkatan < 2018 || (int) $angkatan > $tahunIni) {
        $galat['angkatan'] = "Angkatan harus antara 2018 sampai $tahunIni.";
    }

    $email = $in['email'] ?? null;
    if ($email !== null && $email !== '') {
        if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $galat['email'] = 'Format email tidak valid.';
        }
    }

    return $galat;
}
