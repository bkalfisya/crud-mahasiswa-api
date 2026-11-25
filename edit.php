<?php
// TAHAP 0 — Halaman web biasa: mengubah data mahasiswa (Update).
require __DIR__ . '/koneksi.php';
require __DIR__ . '/validasi.php';

$nim = $_GET['nim'] ?? '';
$st  = $pdo->prepare("SELECT * FROM mahasiswa WHERE nim = ?");
$st->execute([$nim]);
$lama = $st->fetch();
if (!$lama) {
    header('Location: index.php?pesan=' . urlencode('Data tidak ditemukan.'));
    exit;
}

$in    = ['nama' => $lama['nama'], 'prodi' => $lama['prodi'], 'angkatan' => (string) $lama['angkatan'], 'email' => (string) $lama['email']];
$galat = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in    = array_map('trim', array_intersect_key($_POST, $in) + $in);
    $galat = validasiMahasiswa($in, false);   // NIM tidak diubah
    if (!$galat) {
        $up = $pdo->prepare("UPDATE mahasiswa SET nama = ?, prodi = ?, angkatan = ?, email = ? WHERE nim = ?");
        $up->execute([$in['nama'], $in['prodi'], (int) $in['angkatan'], $in['email'] ?: null, $nim]);
        header('Location: index.php?pesan=' . urlencode('Data berhasil diubah.'));
        exit;
    }
}
function e($t) { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Ubah Mahasiswa</title>
  <link rel="stylesheet" href="gaya.css">
</head>
<body>
  <h1>Ubah Mahasiswa <?= e($nim) ?></h1>
  <?php if ($galat): ?>
    <div class="galat"><?php foreach ($galat as $g) echo e($g) . '<br>'; ?></div>
  <?php endif; ?>

  <form method="post">
    <label>Nama</label>
    <input name="nama" value="<?= e($in['nama']) ?>">
    <label>Prodi</label>
    <select name="prodi">
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
