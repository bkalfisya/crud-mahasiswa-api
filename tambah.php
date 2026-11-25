<?php
// TAHAP 0 — Halaman web biasa: menambah mahasiswa (Create).
require __DIR__ . '/koneksi.php';
require __DIR__ . '/validasi.php';

$in    = ['nim' => '', 'nama' => '', 'prodi' => '', 'angkatan' => '', 'email' => ''];
$galat = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in    = array_map('trim', array_intersect_key($_POST, $in) + $in);
    $galat = validasiMahasiswa($in);

    if (!$galat) {
        $cek = $pdo->prepare("SELECT 1 FROM mahasiswa WHERE nim = ?");
        $cek->execute([$in['nim']]);
        if ($cek->fetch()) {
            $galat['nim'] = 'NIM sudah terdaftar.';
        }
    }

    if (!$galat) {
        $st = $pdo->prepare("INSERT INTO mahasiswa (nim, nama, prodi, angkatan, email) VALUES (?, ?, ?, ?, ?)");
        $st->execute([$in['nim'], $in['nama'], $in['prodi'], (int) $in['angkatan'], $in['email'] ?: null]);
        header('Location: index.php?pesan=' . urlencode('Mahasiswa berhasil ditambahkan.'));
        exit;
    }
}
function e($t) { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Mahasiswa</title>
  <link rel="stylesheet" href="gaya.css">
</head>
<body>
  <h1>Tambah Mahasiswa</h1>
  <?php if ($galat): ?>
    <div class="galat"><?php foreach ($galat as $g) echo e($g) . '<br>'; ?></div>
  <?php endif; ?>

  <form method="post">
    <label>NIM</label>
    <input name="nim" maxlength="10" value="<?= e($in['nim']) ?>">
    <label>Nama</label>
    <input name="nama" value="<?= e($in['nama']) ?>">
    <label>Prodi</label>
    <select name="prodi">
      <option value="">-- pilih --</option>
      <?php foreach (DAFTAR_PRODI as $p): ?>
        <option <?= $in['prodi'] === $p ? 'selected' : '' ?>><?= e($p) ?></option>
      <?php endforeach; ?>
    </select>
    <label>Angkatan</label>
    <input name="angkatan" value="<?= e($in['angkatan']) ?>">
    <label>Email (opsional)</label>
    <input name="email" value="<?= e($in['email']) ?>">
    <p><button type="submit">Simpan</button> <a class="tombol sekunder" href="index.php">Batal</a></p>
  </form>
</body>
</html>
