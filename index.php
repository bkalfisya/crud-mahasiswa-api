<?php
// TAHAP 0 — Halaman web biasa: menampilkan daftar mahasiswa (Read).
require __DIR__ . '/koneksi.php';

$cari = trim($_GET['cari'] ?? '');
if ($cari !== '') {
    $st = $pdo->prepare("SELECT * FROM mahasiswa WHERE nim LIKE ? OR nama LIKE ? ORDER BY nim");
    $st->execute(["%$cari%", "%$cari%"]);
} else {
    $st = $pdo->query("SELECT * FROM mahasiswa ORDER BY nim");
}
$daftar = $st->fetchAll();
$pesan  = $_GET['pesan'] ?? '';
function e($t) { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Mahasiswa</title>
  <link rel="stylesheet" href="gaya.css">
</head>
<body>
  <h1>Data Mahasiswa</h1>
  <p class="sub">Versi web biasa — server mengirim halaman HTML yang sudah jadi.</p>

  <?php if ($pesan): ?><div class="pesan"><?= e($pesan) ?></div><?php endif; ?>

  <form class="cari" method="get">
    <input type="text" name="cari" placeholder="Cari NIM atau nama" value="<?= e($cari) ?>">
    <button type="submit">Cari</button>
    <a class="tombol" href="tambah.php">+ Tambah Mahasiswa</a>
    <a class="tombol sekunder" href="klien-api.html">Buka versi yang memakai API</a>
  </form>

  <table>
    <tr><th>NIM</th><th>Nama</th><th>Prodi</th><th>Angkatan</th><th>Email</th><th>Aksi</th></tr>
    <?php foreach ($daftar as $m): ?>
    <tr>
      <td><?= e($m['nim']) ?></td>
      <td><?= e($m['nama']) ?></td>
      <td><?= e($m['prodi']) ?></td>
      <td><?= e($m['angkatan']) ?></td>
      <td><?= e($m['email'] ?? '-') ?></td>
      <td>
        <a class="tombol sekunder" href="edit.php?nim=<?= urlencode($m['nim']) ?>">Ubah</a>
        <!-- Hapus memakai POST, bukan link GET, agar data tidak terhapus tanpa sengaja -->
        <form class="inline" method="post" action="hapus.php" onsubmit="return confirm('Hapus data ini?')">
          <input type="hidden" name="nim" value="<?= e($m['nim']) ?>">
          <button class="bahaya" type="submit">Hapus</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$daftar): ?><tr><td colspan="6">Tidak ada data.</td></tr><?php endif; ?>
  </table>
</body>
</html>
