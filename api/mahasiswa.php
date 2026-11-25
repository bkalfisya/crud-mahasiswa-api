<?php
// Penanganan resource mahasiswa (validasi + proses data + response).

/** Mengubah baris basis data menjadi bentuk JSON yang rapi (angkatan sebagai angka). */
function bentukMahasiswa(array $m): array {
    return [
        'nim'      => $m['nim'],
        'nama'     => $m['nama'],
        'prodi'    => $m['prodi'],
        'angkatan' => (int) $m['angkatan'],
        'email'    => $m['email'],
    ];
}

function cariByNim(PDO $pdo, string $nim): ?array {
    $st = $pdo->prepare("SELECT * FROM mahasiswa WHERE nim = ?");
    $st->execute([$nim]);
    return $st->fetch() ?: null;
}

/** GET /api/mahasiswa?cari=...&prodi=...&angkatan=... */
function daftarMahasiswa(PDO $pdo): void {
    $sql   = "SELECT * FROM mahasiswa WHERE 1=1";
    $nilai = [];
    if (($cari = trim($_GET['cari'] ?? '')) !== '') {
        $sql .= " AND (nim LIKE ? OR nama LIKE ?)";
        array_push($nilai, "%$cari%", "%$cari%");
    }
    if (($prodi = $_GET['prodi'] ?? '') !== '') {
        if (!in_array($prodi, DAFTAR_PRODI, true)) {
            kirim(400, ['error' => 'Prodi tidak dikenal.']);
        }
        $sql .= " AND prodi = ?";
        $nilai[] = $prodi;
    }
    if (($angkatan = $_GET['angkatan'] ?? '') !== '') {
        if (!ctype_digit($angkatan)) {
            kirim(400, ['error' => 'Parameter angkatan harus berupa angka.']);
        }
        $sql .= " AND angkatan = ?";
        $nilai[] = (int) $angkatan;
    }
    $st = $pdo->prepare($sql . " ORDER BY nim");
    $st->execute($nilai);
    $data = array_map('bentukMahasiswa', $st->fetchAll());
    kirim(200, ['jumlah' => count($data), 'data' => $data]);
}

/** GET /api/mahasiswa/{nim} */
function detailMahasiswa(PDO $pdo, string $nim): void {
    $m = cariByNim($pdo, $nim);
    if (!$m) {
        kirim(404, ['error' => "Mahasiswa dengan NIM $nim tidak ditemukan."]);
    }
    kirim(200, ['data' => bentukMahasiswa($m)]);
}

/** POST /api/mahasiswa — admin & operator */
function tambahMahasiswa(PDO $pdo): void {
    wajibLogin($pdo, ['admin', 'operator']);   // cek hak akses dulu
    $in = bacaBody();                          // 1. baca body
    $galat = validasiMahasiswa($in);           // 2. validasi (aturan yang sama dengan web)
    if ($galat) {
        kirim(400, ['error' => 'Data tidak valid.', 'detail' => $galat]);
    }
    if (cariByNim($pdo, $in['nim'])) {
        kirim(409, ['error' => 'NIM sudah terdaftar.']);
    }
    $st = $pdo->prepare("INSERT INTO mahasiswa (nim, nama, prodi, angkatan, email) VALUES (?, ?, ?, ?, ?)");
    $st->execute([$in['nim'], trim($in['nama']), $in['prodi'], (int) $in['angkatan'], ($in['email'] ?? '') ?: null]);
    header('Location: ' . BASE_API . '/mahasiswa/' . $in['nim']);
    kirim(201, ['pesan' => 'Mahasiswa berhasil ditambahkan.', 'data' => bentukMahasiswa(cariByNim($pdo, $in['nim']))]);
}

/** PUT /api/mahasiswa/{nim} — admin & operator. Mengganti seluruh data kecuali NIM. */
function ubahMahasiswa(PDO $pdo, string $nim): void {
    wajibLogin($pdo, ['admin', 'operator']);
    if (!cariByNim($pdo, $nim)) {
        kirim(404, ['error' => "Mahasiswa dengan NIM $nim tidak ditemukan."]);
    }
    $in = bacaBody();
    if (isset($in['nim']) && $in['nim'] !== $nim) {
        kirim(400, ['error' => 'NIM tidak boleh diubah.']);
    }
    $galat = validasiMahasiswa($in, false);
    if ($galat) {
        kirim(400, ['error' => 'Data tidak valid.', 'detail' => $galat]);
    }
    $st = $pdo->prepare("UPDATE mahasiswa SET nama = ?, prodi = ?, angkatan = ?, email = ? WHERE nim = ?");
    $st->execute([trim($in['nama']), $in['prodi'], (int) $in['angkatan'], ($in['email'] ?? '') ?: null, $nim]);
    kirim(200, ['pesan' => 'Data berhasil diubah.', 'data' => bentukMahasiswa(cariByNim($pdo, $nim))]);
}

/** DELETE /api/mahasiswa/{nim} — khusus admin */
function hapusMahasiswa(PDO $pdo, string $nim): void {
    wajibLogin($pdo, ['admin']);
    $st = $pdo->prepare("DELETE FROM mahasiswa WHERE nim = ?");
    $st->execute([$nim]);
    if ($st->rowCount() === 0) {
        kirim(404, ['error' => "Mahasiswa dengan NIM $nim tidak ditemukan."]);
    }
    kirim(204);   // berhasil, tanpa isi
}